<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'sport',
        'content',
        'media_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Dueño del post */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Comentarios de la publicación */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}