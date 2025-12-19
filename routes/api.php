<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MatrixController;
use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/matrix', [MatrixController::class, 'index']);
    Route::get('/matrix/{matrix}', [MatrixController::class, 'show']);
    Route::get('/matrix/{matrix}/calculate', [MatrixController::class, 'calculate']);
    Route::post('/matrix', [MatrixController::class, 'store']);
    Route::put('/matrix/{matrix}', [MatrixController::class, 'update']);
    Route::delete('/matrix/{matrix}', [MatrixController::class, 'destroy']);
});
// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::delete('/user', [AuthController::class, 'destroy'])->middleware('auth:sanctum');
