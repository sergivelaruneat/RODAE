<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Publication;

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

        /** Publicaciones propias */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /** A quién sigo */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    /** Quién me sigue */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    /** Publicaciones a las que di like */
    public function likedPublications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_likes')
            ->withTimestamps();
    }
}
