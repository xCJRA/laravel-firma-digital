<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FirmaController;

Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Documentos
    Route::apiResource('documentos', DocumentoController::class)
         ->only(['index', 'store', 'show', 'destroy']);

    // Sub-recursos de documentos
    Route::post('documentos/{documento}/firmantes',           [DocumentoController::class, 'agregarFirmantes']);
    Route::post('documentos/{documento}/reenviar/{firmante}', [DocumentoController::class, 'reenviarToken']);
    Route::get('documentos/{documento}/auditoria',            [DocumentoController::class, 'auditoria']);
});

// Ruta pública — el firmante no necesita estar autenticado en el sistema
Route::post('/firmar/{token}', [FirmaController::class, 'firmar']);
