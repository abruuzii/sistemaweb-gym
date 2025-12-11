<?php

namespace App\Http\Controllers;

use App\Models\Progreso;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ProgresoController extends Controller
{
    // Registrar progreso (cliente)
    public function store(Request $request)
    {
        $request->validate([
            'ejercicio' => 'required|in:snatch,clean,clean_and_jerk,back_squat,front_squat,press_militar,press_banca,deadlift',
            'marca_maxima' => 'required|numeric|min:0'
        ]);

        $cliente_id = $request->user()->cliente->id;

        $progreso = Progreso::create([
            'cliente_id' => $cliente_id,
            'ejercicio' => $request->ejercicio,
            'marca_maxima' => $request->marca_maxima,
            'fecha' => now()
        ]);

        return response()->json($progreso, 201);
    }

    // Obtener progresos del cliente logueado
    public function misProgresos(Request $request)
    {
        $cliente_id = $request->user()->cliente->id;
        return Progreso::where('cliente_id', $cliente_id)->orderBy('fecha', 'desc')->get();
    }

    // Entrenador: ver progresos de un cliente
    public function progresosCliente($cliente_id)
    {
        return Progreso::where('cliente_id', $cliente_id)->orderBy('fecha', 'desc')->get();
    }
}
