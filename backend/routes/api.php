<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProfileController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

// Auth (público)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/refresh',  [AuthController::class, 'refresh']);

// Password reset (público)
Route::post('/password/forgot', [PasswordController::class, 'forgot']);
Route::post('/password/reset',  [PasswordController::class, 'reset']);

// Verificación de email por enlace firmado (público)
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed','throttle:6,1'])
    ->name('verification.verify');

// Comprobar disponibilidad de username (público)
Route::get('/check-username/{username}', function (string $username) {
    $exists = User::where('username', $username)->exists();
    return response()->json(['available' => ! $exists]);
})
->where('username', '[A-Za-z0-9_\.]{3,30}')
->middleware('throttle:30,1')
->name('username.check');

// Perfil AJENO (público, porque /profile/:user no es ruta privada en tu front)
Route::get('/users/{user}/profile', [ProfileController::class, 'showUser'])->name('users.profile.show');
Route::get('/users/{user}/avatar',  [ProfileController::class, 'avatarUser'])->name('users.avatar');

// Reenviar verificación de email (versión pública para NO logueados, si lo usas)
Route::post('/email/resend-public', [EmailVerificationController::class, 'resendPublic'])
    ->middleware('throttle:6,1')
    ->name('verification.resend.public');


/*
|--------------------------------------------------------------------------
| Protegidas (JWT) -> auth:api
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Reenviar verificación de email (logueado)
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');

    // Perfil PROPIO (dueño)
    Route::get('/profile',         [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',         [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar',  [ProfileController::class, 'avatarSelf'])->name('profile.avatar');

    // ===== Publications =====
    // Feed (seguidos + yo)
    Route::get('/publications/feed', [PublicationController::class, 'feed'])->name('publications.feed');

    // Listado por usuario autenticado o por ?user_id=... (según tu implementación)
    Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');

    // Crear publicación
    Route::post('/publications', [PublicationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('publications.store');

    // Borrar publicación (owner vía Policy/controller)
    Route::delete('/publications/{publication}', [PublicationController::class, 'destroy'])
        ->name('publications.destroy');

    // ===== Comments =====
    // Listar comentarios de una publicación (paginado)
    Route::get('/publications/{publication}/comments',  [CommentController::class, 'index'])
        ->name('comments.index');

    // Crear comentario
    Route::post('/publications/{publication}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('comments.store');

    // Borrar comentario propio
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
});
