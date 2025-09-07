<?php

namespace App\Models;

use App\Enums\Sport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\Builder;

class Routine extends Model
{
    protected $fillable = [
        'owner_user_id',
        'name',
        'sport',
        'rating_avg',
        'exercises_count',
    ];

    protected $casts = [
        'sport' => Sport::class,
        'rating_avg' => 'float',
        'exercises_count' => 'integer',
    ];

    // (Opcional) si quieres que salga automáticamente en ->toArray()
    protected $appends = ['sport_label'];

    /* ======================= Accessors ======================= */

    public function getSportLabelAttribute(): ?string
    {
        return $this->sport?->label();
    }

    /* ======================= Relaciones ======================= */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class)->orderBy('position');
    }

    // usuarios que siguen la rutina (pivot user_routines con 'rating')
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_routines')
            ->withPivot('rating')
            ->withTimestamps();
    }

    /* ========================= Helpers ======================== */

    // Recalcular y guardar la media de valoraciones (1..5) desde el pivot
    public function recalcRatingAvg(): void
    {
        $avg = (float) UserRoutine::where('routine_id', $this->id)
            ->whereNotNull('rating')
            ->avg('rating');

        $this->forceFill(['rating_avg' => round($avg, 2)])->save();
    }

    // Sincroniza el contador de ejercicios
    public function syncExercisesCount(): void
    {
        $this->forceFill(['exercises_count' => $this->exercises()->count()])->save();
    }

    /* ========================= Scopes ========================= */

    // Buscar por nombre
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term ? $q->where('name', 'like', "%{$term}%") : $q;
    }

    // Filtrar por nombre del owner (para “buscar por entrenador”)
    public function scopeOwnerName(Builder $q, ?string $name): Builder
    {
        if (!$name) return $q;
        return $q->whereHas('owner', fn ($w) => $w->where('name', 'like', "%{$name}%"));
    }

    // Filtrar por deporte
    public function scopeSport(Builder $q, ?string $sport): Builder
    {
        return $sport ? $q->where('sport', $sport) : $q;
    }

    // Orden: rating | recent (default)
    public function scopeOrderFor(Builder $q, ?string $order): Builder
    {
        return match ($order) {
            'rating' => $q->orderByDesc('rating_avg'),
            'recent', null, '' => $q->orderByDesc('id'),
            default => $q
        };
    }
}
