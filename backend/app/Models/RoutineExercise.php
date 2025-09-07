<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_id',
        'name',
        'description',   // alias -> se guarda en 'details'
        'series_reps',   // texto libre: "3x10", "100 libres"
        'rest',          // texto libre: "90s", "2–3 min"
        'position',
    ];

    protected $hidden = ['details'];     // ocultamos la columna real
    protected $appends = ['description']; // exponemos 'description' siempre

    protected $casts = [
        'position' => 'integer',
    ];

    /* Relaciones */
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    /* Scopes */
    public function scopeOrdered($q)
    {
        return $q->orderBy('position');
    }

    /* Accessors / Mutators (alias description ↔ details) */
    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['details'] ?? null;
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['details'] = $value;
    }

    /* Hooks */
    protected static function booted(): void
    {
        static::creating(function (self $exercise) {
            if (is_null($exercise->position) && $exercise->routine_id) {
                $max = self::where('routine_id', $exercise->routine_id)->max('position');
                $exercise->position = (int) $max + 1;
            }
        });
    }
}
