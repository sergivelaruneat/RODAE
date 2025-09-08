<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'routine_exercise_id',

        // ---- SNAPSHOT desde la rutina en el momento de crear el reporte ----
        'exercise_name',
        'rest',
        'series_reps',
        'position',

        // ---- Datos introducidos por el usuario en el reporte ----
        'difficulty',
        'metric',
        'completed',
    ];

    protected $casts = [
        'difficulty' => 'integer',
        'position'   => 'integer',
        'completed'  => 'boolean',
    ];

    // Al tocar un item, se “toca” también el updated_at del Report
    protected $touches = ['report'];

    /* ================= Relaciones ================= */

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function routineExercise(): BelongsTo
    {
        return $this->belongsTo(RoutineExercise::class, 'routine_exercise_id');
    }

    /* (Opcional) scope para listarlos en el orden guardado */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('id');
    }
}

