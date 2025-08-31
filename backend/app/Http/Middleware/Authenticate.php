<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Para /api/* devolvemos 401 JSON (sin redirigir).
     * Para rutas web, redirige a login si quieres (opcional).
     */
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return null; // deja que el guard lance 401
        }
        return route('login'); // solo si tienes vistas web
    }
}
