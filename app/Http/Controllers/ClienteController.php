<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\User;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;



class ClienteController extends Controller
{
 public function clientesPorVencer()
{
    $hoy = Carbon::today();
    $diasAviso = 7;

    $clientes = Cliente::with([
        'membresia',
        'transacciones' => function ($q) {
            $q->where('tipo', 'pago')
              ->orderBy('fecha', 'desc');
        }
    ])->get();

    $resultado = [];

    foreach ($clientes as $cliente) {
        $ultimaTransaccion = $cliente->transacciones->first();
        $membresia = $cliente->membresia;

        if (!$ultimaTransaccion || !$membresia) continue;

        $fechaInicio = $ultimaTransaccion->fecha
            ? Carbon::parse($ultimaTransaccion->fecha)->startOfDay()
            : $ultimaTransaccion->created_at->startOfDay();

        $duracionMeses = (int) ($membresia->duracion_meses ?? 1);
        if ($duracionMeses <= 0) $duracionMeses = 1;

        $fechaFin = $fechaInicio->copy()->addMonthsNoOverflow($duracionMeses);

        $diasRestantes = $hoy->diffInDays($fechaFin, false);

        if ($diasRestantes >= 0 && $diasRestantes <= $diasAviso) {
            $resultado[] = [
                'cliente_id'        => $cliente->id,
                // ✅ nombre real del cliente
                'nombre'            => $cliente->nombre,
                'apellido'          => $cliente->apellido,
                'cedula'            => $cliente->cedula_identidad,
                'correo'            => $cliente->correo,
                'membresia'         => $membresia->nombre,
                'fecha_ultimo_pago' => $fechaInicio->toDateString(),
                'fecha_fin'         => $fechaFin->toDateString(),
                'dias_restantes'    => $diasRestantes,
            ];
        }
    }

    return response()->json($resultado);
}
 public function clientesVencidos()
{
    $hoy = Carbon::today();

    $clientes = Cliente::with([
        'membresia',
        'transacciones' => function ($q) {
            $q->where('tipo', 'pago')
              ->orderBy('fecha', 'desc');
        }
    ])->get();

    $resultado = [];

    foreach ($clientes as $cliente) {
        $ultimaTransaccion = $cliente->transacciones->first();
        $membresia = $cliente->membresia;

        if (!$ultimaTransaccion || !$membresia) continue;

        $fechaInicio = $ultimaTransaccion->fecha
            ? Carbon::parse($ultimaTransaccion->fecha)->startOfDay()
            : $ultimaTransaccion->created_at->startOfDay();

        // ✅ usar duracion_meses (no duracion_dias) porque estás sumando meses
        $duracionMeses = (int) ($membresia->duracion_meses ?? 1);
        if ($duracionMeses <= 0) $duracionMeses = 1;

        $fechaFin = $fechaInicio->copy()->addMonthsNoOverflow($duracionMeses);

        // ✅ mejor: días vencidos, no meses (más preciso para dashboard)
        $diasVencidos = $fechaFin->diffInDays($hoy, false); // positivo si ya venció

        if ($diasVencidos > 0) {
            $resultado[] = [
                'cliente_id'        => $cliente->id,
                // ✅ nombre real del cliente
                'nombre'            => $cliente->nombre,
                'apellido'          => $cliente->apellido,
                'cedula'            => $cliente->cedula_identidad,
                'correo'            => $cliente->correo,
                'membresia'         => $membresia->nombre,
                'fecha_ultimo_pago' => $fechaInicio->toDateString(),
                'fecha_fin'         => $fechaFin->toDateString(),
                // ✅ tu front ya calcula, pero si quieres mandarlo:
                'dias_vencidos'     => $diasVencidos,
            ];
        }
    }

    return response()->json($resultado);
}


    /**
     * Listar todos los clientes (para admin).
     */
    public function index()
    {
        $clientes = Cliente::with('membresia', 'usuario')->get();
        return response()->json($clientes);
    }

    /**
     * Crear un nuevo cliente + crear pago inicial de su membresía.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'usuario_id'        => 'required|exists:users,id',
        'nombre'            => 'required|string|max:255',
        'apellido'          => 'required|string|max:255',
        'cedula_identidad'  => 'required|string|max:255|unique:clientes,cedula_identidad',
        'telefono'          => 'required|string|max:255',
        'direccion'         => 'required|string',
        'correo'            => 'required|email|max:255|unique:clientes,correo',
        'fecha_nacimiento'  => 'required|date',
        'estado'            => 'required|in:activo,inactivo,pendiente,suspendido',
        'peso'              => 'nullable|numeric',
        'altura'            => 'nullable|numeric',
        'condicion_medica'  => 'nullable|string',
        'membresia_id'      => 'required|exists:membresias,id',
        'monto_inicial'     => 'nullable|numeric|min:0',
        'descripcion_pago'  => 'nullable|string',
    ]);

    if ($request->has('foto')) {
        $validated['foto'] = $request->input('foto');
    }

    return DB::transaction(function () use ($validated) {

        // 🔹 AQUÍ añadimos nombre y apellido al create()
        $cliente = Cliente::create([
            'usuario_id'        => $validated['usuario_id'],
            'nombre'            => $validated['nombre'],
            'apellido'          => $validated['apellido'],
            'cedula_identidad'  => $validated['cedula_identidad'],
            'telefono'          => $validated['telefono'],
            'direccion'         => $validated['direccion'],
            'correo'            => $validated['correo'],
            'fecha_nacimiento'  => $validated['fecha_nacimiento'],
            'estado'            => $validated['estado'],
            'peso'              => $validated['peso'] ?? null,
            'altura'            => $validated['altura'] ?? null,
            'condicion_medica'  => $validated['condicion_medica'] ?? null,
            'membresia_id'      => $validated['membresia_id'],
            'foto'              => $validated['foto'] ?? 'cliente_default.jpg',
        ]);

        $membresia   = Membresia::findOrFail($validated['membresia_id']);
        $montoInicial = $validated['monto_inicial'] ?? $membresia->precio;

        Transaccion::create([
            'cliente_id'   => $cliente->id,
            'membresia_id' => $membresia->id,
            'monto'        => $montoInicial,
            'tipo'         => 'pago',
            'descripcion'  => $validated['descripcion_pago'] ?? 'Pago inicial al crear el cliente',
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente con pago inicial.',
            'cliente' => $cliente->load('membresia', 'usuario'),
        ], 201);
    });
}

    public function show(Cliente $cliente)
    {
        $cliente->load('membresia', 'usuario');
        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'usuario_id'       => 'sometimes|exists:users,id',
            'cedula_identidad' => 'sometimes|string|max:255|unique:clientes,cedula_identidad,' . $cliente->id,
            'telefono'         => 'sometimes|string|max:255',
            'direccion'        => 'sometimes|string',
            'correo'           => 'sometimes|email|max:255|unique:clientes,correo,' . $cliente->id,
            'fecha_nacimiento' => 'sometimes|date',
            'estado'           => 'sometimes|in:activo,inactivo,pendiente,suspendido',
            'peso'             => 'nullable|numeric',
            'altura'           => 'nullable|numeric',
            'condicion_medica' => 'nullable|string',
            'membresia_id'     => 'sometimes|exists:membresias,id',
        ]);

        if ($request->has('foto')) {
            $validated['foto'] = $request->input('foto');
        }

        $cliente->update($validated);

        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'cliente' => $cliente,
        ]);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return response()->json([
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $cliente = Cliente::where('correo', $user->email)
            ->with('membresia')
            ->first();

        if (!$cliente) {
            return response()->json(['message' => 'No se encontró cliente asociado.'], 404);
        }

        return response()->json($cliente);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $cliente = Cliente::where('correo', $user->email)->first();

        if (!$cliente) {
            return response()->json(['message' => 'No se encontró cliente asociado.'], 404);
        }

        $validated = $request->validate([
            'telefono'         => 'sometimes|string|max:255',
            'direccion'        => 'sometimes|string',
            'peso'             => 'nullable|numeric',
            'altura'           => 'nullable|numeric',
            'condicion_medica' => 'nullable|string',
        ]);

        $cliente->update($validated);

        return response()->json([
            'message' => 'Perfil de cliente actualizado correctamente.',
            'cliente' => $cliente,
        ]);
    }
}
