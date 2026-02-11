<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::patch('/users/{id}', [UserController::class, 'update']); // PATCH y PUT usan el mismo método
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Ruta especial para restaurar eliminados lógicamente
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
});
