<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable;

    protected $fillable = ['name','username','email','password','role'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed', // Laravel 10+: hash automático al asignar
    ];

    public function profile(){ return $this->hasOne(Profile::class); }

    // JWT
    public function getJWTIdentifier(){ return $this->getKey(); }
    public function getJWTCustomClaims(){ return []; }
}
