<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use Illuminate\Http\Request;

class MembresiaController extends Controller
{
    /**
     * Mostrar todas las membresías.
     */
    public function index()
    {
        $membresias = Membresia::all();
        return response()->json($membresias, 200);
    }

    /**
     * Crear una nueva membresía.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_dias' => 'required|integer|min:1',
            'activo' => 'boolean'
        ]);

        $membresia = Membresia::create($validated);

        return response()->json([
            'message' => 'Membresía creada exitosamente.',
            'data' => $membresia
        ], 201);
    }

    /**
     * Mostrar una membresía específica.
     */
    public function show($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada.'], 404);
        }

        return response()->json($membresia, 200);
    }

    /**
     * Actualizar una membresía existente.
     */
    public function update(Request $request, $id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'duracion_dias' => 'sometimes|integer|min:1',
            'activo' => 'boolean'
        ]);

        $membresia->update($validated);

        return response()->json([
            'message' => 'Membresía actualizada correctamente.',
            'data' => $membresia
        ], 200);
    }

    /**
     * Eliminar una membresía.
     */
    public function destroy($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return response()->json(['message' => 'Membresía no encontrada.'], 404);
        }

        $membresia->delete();

        return response()->json(['message' => 'Membresía eliminada correctamente.'], 200);
    }
}
