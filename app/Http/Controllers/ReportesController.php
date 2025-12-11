<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    // GET /api/reportes/clientes?from=2025-11-01&to=2025-11-30&group=day|month
    public function clientes(Request $request)
    {
        $data = $request->validate([
            'from'  => 'required|date',
            'to'    => 'required|date|after_or_equal:from',
            'group' => 'nullable|in:day,month',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        $group = $data['group'] ?? 'day';

        // Agrupar por día o por mes usando created_at de clientes
        $format = $group === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $series = Cliente::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as etiqueta, COUNT(*) as total")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('etiqueta')
            ->orderBy('etiqueta')
            ->get();

        $total = $series->sum('total');

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'group' => $group,
            'total' => $total,
            'series' => $series, // [{etiqueta:'2025-11-01', total:3}, ...]
        ]);
    }

    // GET /api/reportes/ingresos?from=2025-11-01&to=2025-11-30&group=day|month
    public function ingresos(Request $request)
    {
        $data = $request->validate([
            'from'  => 'required|date',
            'to'    => 'required|date|after_or_equal:from',
            'group' => 'nullable|in:day,month',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        $group = $data['group'] ?? 'day';

        // Usa "fecha" (timestamp) de transacciones
        $format = $group === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $series = Transaccion::query()
            ->selectRaw("DATE_FORMAT(fecha, '{$format}') as etiqueta, SUM(monto) as total")
            ->where('tipo', 'pago')
            ->whereBetween('fecha', [$from, $to])
            ->groupBy('etiqueta')
            ->orderBy('etiqueta')
            ->get();

        $total = $series->sum('total');

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'group' => $group,
            'total' => (string) $total,
            'series' => $series, // [{etiqueta:'2025-11-01', total:'35.00'}, ...]
        ]);
    }
}
