<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'], // cast 'hashed' lo cifra
            'role'     => $data['role'] ?? 'user',
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json(['user' => $user], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user = auth('api')->user();

        // si quieres exigir email verificado antes de login:
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            auth('api')->logout();
            return response()->json(['message' => 'Debes verificar tu correo antes de iniciar sesión'], 403);
        }

        return response()->json([
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => auth('api')->factory()->getTTL() * 60,
            'user'        => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(auth('api')->user());
    }

    public function refresh()
    {
        return response()->json([
            'token'       => auth('api')->refresh(),
            'token_type'  => 'bearer',
            'expires_in'  => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logout OK']);
    }
}
