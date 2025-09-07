<?php

namespace App\Observers;

use App\Models\Routine;
use App\Models\RoutineExercise;

class RoutineExerciseObserver
{
    public function created(RoutineExercise $ex): void
    {
        $ex->routine?->syncExercisesCount();
    }

    public function deleted(RoutineExercise $ex): void
    {
        $ex->routine?->syncExercisesCount();
    }

    public function restored(RoutineExercise $ex): void
    {
        $ex->routine?->syncExercisesCount();
    }

    public function forceDeleted(RoutineExercise $ex): void
    {
        $ex->routine?->syncExercisesCount();
    }

    // Por si mueves un ejercicio a otra rutina
    public function updated(RoutineExercise $ex): void
    {
        if ($ex->wasChanged('routine_id')) {
            // sincroniza la rutina anterior y la nueva
            if ($oldId = $ex->getOriginal('routine_id')) {
                if ($old = Routine::find($oldId)) {
                    $old->syncExercisesCount();
                }
            }
            $ex->routine?->syncExercisesCount();
        }
    }
}
