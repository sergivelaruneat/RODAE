<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\UserRoutineController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/refresh',  [AuthController::class, 'refresh']);

// Recuperación de contraseña
Route::post('/password/forgot', [PasswordController::class, 'forgot']);
Route::post('/password/reset',  [PasswordController::class, 'reset']);

// Verificación de email
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed','throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/resend-public', [EmailVerificationController::class, 'resendPublic'])
    ->middleware('throttle:6,1')
    ->name('verification.resend.public');

// Utilidades
Route::get('/check-username/{username}', function (string $username) {
    $exists = User::where('username', $username)->exists();
    return response()->json(['available' => ! $exists]);
})
->where('username', '[A-Za-z0-9_\.]{3,30}')
->middleware('throttle:30,1')
->name('username.check');

// Perfiles públicos
Route::get('/users/{user}/profile', [ProfileController::class, 'showUser'])->name('users.profile.show');
Route::get('/users/{user}/avatar',  [ProfileController::class, 'avatarUser'])->name('users.avatar');

// Rutinas públicas
Route::get('/routines', [RoutineController::class, 'index']);
Route::get('/routines/{routine}', [RoutineController::class, 'show']);
Route::get('/meta/sport', [RoutineController::class, 'sports']);

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (auth:api)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Gestión de Usuario
    |--------------------------------------------------------------------------
    */
    // Autenticación
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Verificación de email
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');

    // Perfil propio
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar', [ProfileController::class, 'avatarSelf'])->name('profile.avatar');

    /*
    |--------------------------------------------------------------------------
    | Publicaciones y Comentarios
    |--------------------------------------------------------------------------
    */
    // Publicaciones
    Route::get('/publications/feed', [PublicationController::class, 'feed'])->name('publications.feed');
    Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');
    Route::post('/publications', [PublicationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('publications.store');
    Route::delete('/publications/{publication}', [PublicationController::class, 'destroy'])
        ->name('publications.destroy');

    // Comentarios
    Route::get('/publications/{publication}/comments', [CommentController::class, 'index'])
        ->name('comments.index');
    Route::post('/publications/{publication}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Gestión de Rutinas
    |--------------------------------------------------------------------------
    */
    // ---------------- Rutinas ----------------
    // CRUD (solo trainer via policy)
    Route::post('/routines',            [RoutineController::class, 'store']);
    Route::put('/routines/{routine}',   [RoutineController::class, 'update']);
    Route::delete('/routines/{routine}',[RoutineController::class, 'destroy']);

    // Mis rutinas seguidas (athlete/trainer)
    Route::get('/me/routines',          [RoutineController::class, 'myRoutines']);

    // Seguir / dejar de seguir (idempotente, dispara observer)
    Route::post('/routines/{routine}/follow',  [RoutineController::class, 'attach']);
    Route::delete('/routines/{routine}/follow',[RoutineController::class, 'detach']);

    // Valorar / quitar valoración
    Route::post('/routines/{routine}/rate',    [RoutineController::class, 'rate']);
    Route::delete('/routines/{routine}/rate',  [RoutineController::class, 'unrate']);

    // Asignar a otro usuario (trainer propietario) y desasignar
    Route::post('/routines/{routine}/assign',                   [RoutineController::class, 'assign']);
    Route::delete('/users/{user}/routines/{routine}',           [RoutineController::class, 'unassign']); // método nuevo en RoutineController

    // Mis rutinas creadas (trainer)
    Route::get('/me/routines/created', [RoutineController::class, 'created']);

    Route::get('/users/{user}/routines/followed', [RoutineController::class, 'followedByUser']);
    Route::get('/users/{user}/routines/created',  [RoutineController::class, 'createdByUser']);

});
