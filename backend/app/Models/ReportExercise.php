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
        'difficulty',
        'metric',
        'completed',
    ];

    protected $casts = [
        'difficulty' => 'integer',
        'completed'  => 'boolean',
    ];

    protected $touches = ['report'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function routineExercise(): BelongsTo
    {
        return $this->belongsTo(RoutineExercise::class, 'routine_exercise_id');
    }
}
