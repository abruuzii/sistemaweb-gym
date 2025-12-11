<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EntrenamientoController;
use App\Http\Controllers\ProgresoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportesController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {


Route::get('reportes/clientes', [ReportesController::class, 'clientes'])
    ->middleware('role:admin,recepcionista');

Route::get('reportes/ingresos', [ReportesController::class, 'ingresos'])
    ->middleware('role:admin,recepcionista');


    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // ===== DASHBOARD (admin + recepcionista) =====
    Route::get('dashboard/resumen', [DashboardController::class, 'resumen'])
        ->middleware('role:admin,recepcionista');

    Route::get('clientes/por-vencer', [ClienteController::class, 'clientesPorVencer'])
        ->middleware('role:admin,recepcionista');

    Route::get('clientes/vencidos', [ClienteController::class, 'clientesVencidos'])
        ->middleware('role:admin,recepcionista');

    // ===== USUARIOS (solo admin) =====
    Route::apiResource('usuarios', UsuarioController::class)->middleware('role:admin');
    Route::put('usuarios/{id}/toggle-active', [UsuarioController::class, 'toggleActive'])
        ->middleware('role:admin');

    // ===== MEMBRESIAS =====
    // ver membresías: admin/recep/entrenador
    Route::get('membresias', [MembresiaController::class, 'index'])
        ->middleware('role:admin,recepcionista,entrenador');

    // administrar membresías: SOLO admin
    Route::post('membresias', [MembresiaController::class, 'store'])->middleware('role:admin');
    Route::put('membresias/{membresia}', [MembresiaController::class, 'update'])->middleware('role:admin');
    Route::delete('membresias/{membresia}', [MembresiaController::class, 'destroy'])->middleware('role:admin');

    // ===== CLIENTES =====
    // entrenador puede VER clientes, pero NO crear/editar/eliminar
    Route::get('clientes', [ClienteController::class, 'index'])
        ->middleware('role:admin,recepcionista,entrenador');

    Route::get('clientes/{cliente}', [ClienteController::class, 'show'])
        ->middleware('role:admin,recepcionista,entrenador');

    // crear/editar/eliminar SOLO admin + recepcionista
    Route::post('clientes', [ClienteController::class, 'store'])->middleware('role:admin,recepcionista');
    Route::put('clientes/{cliente}', [ClienteController::class, 'update'])->middleware('role:admin,recepcionista');
    Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->middleware('role:admin,recepcionista');

    // ===== TRANSACCIONES (PAGOS) =====
    // ver historial por cliente: admin/recep/entrenador (solo lectura)
    Route::get('transacciones/cliente/{cliente_id}', [TransaccionController::class, 'transaccionesCliente'])
        ->middleware('role:admin,recepcionista,entrenador');

    // registrar pago: admin + recepcionista
    Route::post('transacciones', [TransaccionController::class, 'store'])
        ->middleware('role:admin,recepcionista');

    // listado total (si lo usas): solo admin
    Route::get('transacciones', [TransaccionController::class, 'index'])->middleware('role:admin');

    
});

