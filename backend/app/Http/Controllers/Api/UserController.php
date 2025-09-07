<?php

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // app/Http/Controllers/Api/UserController.php
    public function index(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $per = $request->integer('per_page', 30);

        return \App\Models\User::query()
            ->with('profile:id,user_id,updated_at')   // <- para saber cuándo cambió el avatar
            ->select('id','name','email','role','username')
            ->when($q !== '', fn($qq) =>
                $qq->where('name','like',"%{$q}%")
                ->orWhere('email','like',"%{$q}%")
            )

            ->orderBy('name')
            ->paginate($per)
            ->through(function($u){
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'username' => $u->username,
                    // versión de avatar (timestamp); null si no hay perfil
                    'avatar_updated_at' => optional($u->profile?->updated_at)->timestamp,
                ];
            });
        }

}
