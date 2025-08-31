<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Models\User;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/refresh',  [AuthController::class, 'refresh']);

Route::post('/password/forgot', [PasswordController::class, 'forgot']);
Route::post('/password/reset',  [PasswordController::class, 'reset']);

// Verificación de email (no requiere estar logueado, pero requiere enlace firmado)
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed','throttle:6,1'])
    ->name('verification.verify');
// Disponibilidad de username
Route::get('/check-username/{username}', function (string $username) {
    $exists = User::where('username', $username)->exists();
    return response()->json(['available' => ! $exists]);
})
->where('username', '[A-Za-z0-9_\.]{3,30}') // ajusta patrón/longitud a tu TFG
->middleware('throttle:30,1')               // 30 req/min por IP
->name('username.check');

// Rutas protegidas por JWT
Route::middleware('auth:api')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Reenviar correo de verificación
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
    
});
// Reenviar verificación por email (sin auth) — solo si NO está verificado
Route::post('/email/resend-public', [EmailVerificationController::class, 'resendPublic'])
    ->middleware('throttle:6,1');

