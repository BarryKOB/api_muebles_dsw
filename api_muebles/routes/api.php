<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\MuebleController;
use App\Http\Controllers\Api\CategoriaController;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============================================================
// Ruta temporal para generar tokens de prueba (como lección 2)
// ELIMINAR cuando se integre con la API de Usuarios
// ============================================================
Route::get('test/registrar', function () {

    // Usuario Admin (todas las abilities)
    $admin = User::firstOrCreate(
        ['email' => 'admin@tienda.com'],
        ['name' => 'Admin', 'password' => Hash::make('password')]
    );
    $admin->tokens()->delete();
    $adminToken = $admin->createToken('admin-token', [
        'perfil.ver', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar', 'admin.panel'
    ]);

    // Usuario Gestor (gestión de muebles)
    $gestor = User::firstOrCreate(
        ['email' => 'gestor@tienda.com'],
        ['name' => 'Gestor', 'password' => Hash::make('password')]
    );
    $gestor->tokens()->delete();
    $gestorToken = $gestor->createToken('gestor-token', [
        'perfil.ver', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar'
    ]);

    // Usuario Cliente (solo ver)
    $cliente = User::firstOrCreate(
        ['email' => 'cliente@tienda.com'],
        ['name' => 'Cliente', 'password' => Hash::make('password')]
    );
    $cliente->tokens()->delete();
    $clienteToken = $cliente->createToken('cliente-token', [
        'perfil.ver', 'muebles.ver', 'carrito.gestionar', 'pedidos.crear'
    ]);

    return response()->json([
        'admin'   => $adminToken->plainTextToken,
        'gestor'  => $gestorToken->plainTextToken,
        'cliente' => $clienteToken->plainTextToken,
    ]);
});

// ============================================================
// Rutas públicas (sin token) — prefijo /api/v1
// ============================================================
Route::prefix('v1')->group(function () {

    // Muebles: listar y ver detalle (público, como indica el PDF)
    Route::get('muebles', [MuebleController::class, 'index']);
    Route::get('muebles/{mueble}', [MuebleController::class, 'show']);

    // Categorías: listar y ver detalle (público)
    Route::get('categorias', [CategoriaController::class, 'index']);
    Route::get('categorias/{categoria}', [CategoriaController::class, 'show']);
});

// ============================================================
// Rutas protegidas (con token + abilities) — prefijo /api/v1
// Validadas con auth:sanctum y FormRequests con tokenCan()
// ============================================================
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Muebles: crear, editar, eliminar (requiere abilities)
    Route::post('muebles', [MuebleController::class, 'store']);
    Route::put('muebles/{mueble}', [MuebleController::class, 'update']);
    Route::delete('muebles/{mueble}', [MuebleController::class, 'destroy']);

    // Categorías: crear, editar, eliminar (requiere abilities)
    Route::post('categorias', [CategoriaController::class, 'store']);
    Route::put('categorias/{categoria}', [CategoriaController::class, 'update']);
    Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy']);
});
