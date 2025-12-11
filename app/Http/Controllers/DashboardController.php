<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function resumen()
    {
        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        // 1) Totales básicos
        $totalUsuarios = User::count();
        $totalClientes = Cliente::count();

        // 2) Ingresos del mes (solo pagos, sin devoluciones)
        $ingresosMes = Transaccion::where('tipo', 'pago')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('monto');

        // 3) Clientes cuya membresía vence pronto (próximos 7 días)
        $diasAviso = 7;

        $clientes = Cliente::with(['membresia', 'transacciones' => function ($q) {
            $q->where('tipo', 'pago')->orderBy('fecha', 'desc');
        }])->get();

        $porVencer = [];

        foreach ($clientes as $cliente) {
            $ultimaTransaccion = $cliente->transacciones->first();
            $membresia = $cliente->membresia;

            if (!$ultimaTransaccion || !$membresia) {
                continue;
            }

            $fechaInicio = Carbon::parse($ultimaTransaccion->fecha);
            $fechaFin = $fechaInicio->copy()->addDays($membresia->duracion_dias);
            $diasRestantes = $hoy->diffInDays($fechaFin, false);

            if ($diasRestantes >= 0 && $diasRestantes <= $diasAviso) {
                $porVencer[] = [
                    'cliente_id'      => $cliente->id,
                    'cedula'          => $cliente->cedula_identidad,
                    'correo'          => $cliente->correo,
                    'membresia'       => $membresia->nombre,
                    'fecha_ultimo_pago' => $fechaInicio->toDateString(),
                    'fecha_fin'       => $fechaFin->toDateString(),
                    'dias_restantes'  => $diasRestantes,
                ];
            }
        }

        return response()->json([
            'total_usuarios'       => $totalUsuarios,
            'total_clientes'       => $totalClientes,
            'ingresos_mes'         => $ingresosMes,
            'clientes_por_vencer'  => $porVencer,
        ]);
    }
}
