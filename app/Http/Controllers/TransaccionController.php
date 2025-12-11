<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransaccionController extends Controller
{
    /**
     * Listar todas las transacciones (para admin).
     */
    public function index()
    {
        $transacciones = Transaccion::with('cliente', 'membresia')
            ->get()
            ->map(function ($t) {

                // Calcular fecha_fin si corresponde
                return $this->agregarFechas($t);
            });

        return response()->json($transacciones);
    }

    /**
     * Registrar un pago / devolución.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'cliente_id'      => 'required|exists:clientes,id',
        'membresia_id'    => 'required|exists:membresias,id',
        'monto'           => 'required|numeric|min:0',
        'tipo'            => 'required|in:pago,devolucion',
        'descripcion'     => 'nullable|string',

        'tipo_pago'       => 'required|in:efectivo,transferencia',
        // ✅ Si es transferencia, obligar comprobante
        'comprobante_url' => 'nullable|url|required_if:tipo_pago,transferencia',

        'fecha_manual'    => 'nullable|date',
    ]);

    $transaccion = new Transaccion([
        'cliente_id'      => $validated['cliente_id'],
        'membresia_id'    => $validated['membresia_id'],
        'monto'           => $validated['monto'],
        'tipo'            => $validated['tipo'],
        'descripcion'     => $validated['descripcion'] ?? null,
        'tipo_pago'       => $validated['tipo_pago'],
        'comprobante_url' => $validated['comprobante_url'] ?? null,

    ]);

    // fecha
    if (!empty($validated['fecha_manual'])) {
        $transaccion->fecha = $validated['fecha_manual'];
    } else {
        // opcional: si quieres que siempre tenga fecha
        $transaccion->fecha = now();
    }

 $transaccion->save();

// ✅ recargar desde BD (incluye columnas reales y defaults)
$transaccion = $transaccion->fresh()->load('membresia');

// ✅ agregar fechas como en el historial
$transaccion = $this->agregarFechas($transaccion);

return response()->json([
    'message'     => 'Transacción registrada correctamente.',
    'transaccion' => $transaccion,
], 201);

}



    /**
     * Historial de pagos de un cliente
     */
    public function transaccionesCliente($clienteId)
    {
        $transacciones = Transaccion::with('membresia')
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($t) {
                return $this->agregarFechas($t);
            });

        return response()->json($transacciones);
    }


    /**
     * --------------------------
     * MÉTODO PRIVADO CENTRALIZADO
     * --------------------------
     * Añade fecha_inicio y fecha_fin a cualquier transacción.
     * Esto asegura que TODO el sistema sea consistente.
     */
    private function agregarFechas($t)
    {
        // Fecha de inicio = fecha del pago (o created_at)
        $fechaInicio = $t->fecha
            ? Carbon::parse($t->fecha)->startOfDay()
            : Carbon::parse($t->created_at)->startOfDay();

        $t->fecha_inicio = $fechaInicio->toDateString();

        // Solo calculamos fecha_fin cuando es tipo pago
        if ($t->tipo === 'pago' && $t->membresia) {

            // AHORA usamos duracion_meses (ANTES duracion_dias)
            $meses = (int) ($t->membresia->duracion_meses ?? 1);
            if ($meses < 1) $meses = 1; // seguridad

            // Sumar meses por calendario
            $fechaFin = $fechaInicio
                ->copy()
                ->addMonthsNoOverflow($meses)
                ->toDateString();

            $t->fecha_fin = $fechaFin;
        } else {
            $t->fecha_fin = null;
        }

        return $t;
    }
}
