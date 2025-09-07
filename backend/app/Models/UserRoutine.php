<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoutine extends Model
{
    protected $table = 'user_routines';

    // PK auto-incremental + timestamps ya los gestiona Model por defecto
    protected $fillable = ['user_id','routine_id','rating'];

    protected $casts = [
        'rating' => 'integer', // 1..5 o null
    ];

    /* Relaciones */
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function routine(): BelongsTo { return $this->belongsTo(Routine::class); }

    /* Scopes útiles (opcionales) */
    public function scopeOfUser($q, int $userId)    { return $q->where('user_id', $userId); }
    public function scopeOfRoutine($q, int $rid)    { return $q->where('routine_id', $rid); }
    public function scopeRated($q)                  { return $q->whereNotNull('rating'); }

    /* Normaliza rating a 1..5 o null */
    public function setRatingAttribute($value): void
    {
        if ($value === null || $value === '') { $this->attributes['rating'] = null; return; }
        $this->attributes['rating'] = max(1, min(5, (int) $value));
    }
}
