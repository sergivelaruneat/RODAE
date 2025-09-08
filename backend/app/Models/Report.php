<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'routine_id',
        // si más adelante añades performed_at, inclúyelo aquí
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ================= Relaciones ================= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReportExercise::class);
    }

    /* ================= Scopes útiles =============== */

    /** Reportes de un usuario concreto */
    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    /** Reportes dentro de un rango de fechas (para el calendario) */
    public function scopeBetween($q, Carbon $from, Carbon $to)
    {
        return $q->whereBetween('created_at', [$from, $to]);
    }

    /** Reportes del mes dado (YYYY-MM) */
    public function scopeForYearMonth($q, int $year, int $month)
    {
        $from = Carbon::create($year, $month, 1)->startOfDay();
        $to   = (clone $from)->endOfMonth()->endOfDay();
        return $this->scopeBetween($q, $from, $to);
    }
}

