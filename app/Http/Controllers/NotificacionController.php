<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Crear notificación manual o automática
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'mensaje' => 'required|string',
            'tipo' => 'required|in:informacion,advertencia,urgente'
        ]);

        $notificacion = Notificacion::create($request->all());
        return response()->json($notificacion, 201);
    }

    // Consultar notificaciones del cliente logueado
    public function index(Request $request)
    {
        $cliente_id = $request->user()->cliente->id;
        return Notificacion::where('cliente_id', $cliente_id)->orderBy('fecha_envio', 'desc')->get();
    }

    // Marcar notificación como leída
    public function marcarLeida($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->leido = true;
        $notificacion->save();
        return response()->json(['message' => 'Notificación marcada como leída']);
    }
}
