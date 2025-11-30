<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MatrixController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/matrix', [MatrixController::class, 'index']);
    Route::get('/matrix/{matrix}', [MatrixController::class, 'show']);
    Route::get('/matrix/{matrix}/calculate', [MatrixController::class, 'calculate']);
    Route::post('/matrix', [MatrixController::class, 'store']);
    Route::put('/matrix/{matrix}', [MatrixController::class, 'update']);
    Route::delete('/matrix/{matrix}', [MatrixController::class, 'destroy']);
});
// Auth Routes
Route::post('/register', function(Request $request) {
    $fields = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|unique:users,email',
        'password' => 'required|string|min:6'
    ]);

    $user = User::create([
        'name' => $fields['name'],
        'email' => $fields['email'],
        'password' => Hash::make($fields['password']),
    ]);

    return [
        'message' => 'User registered successfully'
    ];
});

Route::post('/login', function(Request $request) {
    $fields = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string'
    ]);

    $user = User::where('email', $fields['email'])->first();

    if (!$user || !Hash::check($fields['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return [
        'token' => $token,
        'user' => $user
    ];
});

// DELETE ACCOUNT
Route::delete('/user', function(Request $request) {
    $request->user()->delete();
    return ['message' => 'Account deleted'];
})->middleware('auth:sanctum');