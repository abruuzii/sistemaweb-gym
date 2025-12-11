<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Mostrar todos los usuarios (solo admin).
     */
    public function index()
    {
        $usuarios = User::select('id', 'nombre', 'apellido', 'email', 'usuario', 'rol', 'is_active', 'imagen_perfil', 'created_at')
                        ->get();

        return response()->json($usuarios, 200);
    }

    /**
     * Crear un nuevo usuario del sistema.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'usuario' => 'required|string|unique:users,usuario',
            'password' => 'required|string|min:8',
            'rol' => ['required', Rule::in(['admin', 'entrenador', 'recepcionista'])],
            'imagen_perfil' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $usuario = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => $usuario
        ], 201);
    }

    /**
     * Mostrar un usuario específico.
     */
    public function show($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        return response()->json($usuario, 200);
    }

    /**
     * Actualizar un usuario.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'email' => ['sometimes','email', Rule::unique('users')->ignore($usuario->id)],
            'usuario' => ['sometimes','string', Rule::unique('users')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8',
            'rol' => ['sometimes', Rule::in(['admin', 'entrenador', 'recepcionista'])],
            'imagen_perfil' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $usuario
        ], 200);
    }

    /**
     * Eliminar usuario del sistema.
     */
    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.'], 200);
    }

    /**
     * Activar o desactivar usuario rápidamente.
     */
    public function toggleActive($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $usuario->is_active = !$usuario->is_active;
        $usuario->save();

        return response()->json([
            'message' => $usuario->is_active 
                ? 'Usuario activado correctamente.' 
                : 'Usuario desactivado correctamente.',
            'data' => $usuario
        ], 200);
    }
}
