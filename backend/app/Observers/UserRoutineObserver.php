<?php

namespace App\Observers;

use App\Models\UserRoutine;
use App\Models\Routine;

class UserRoutineObserver
{
    /**
     * Cuando se crea el vínculo user↔routine.
     * Si ya viene con rating, recalculamos la media.
     */
    public function created(UserRoutine $userRoutine): void
    {
        if (!is_null($userRoutine->rating)) {
            $this->recalc($userRoutine);
        }
    }

    /**
     * Cuando se actualiza el vínculo.
     * Solo recalculamos si cambió el rating.
     */
    public function updated(UserRoutine $userRoutine): void
    {
        if ($userRoutine->wasChanged('rating')) {
            $this->recalc($userRoutine);
        }
    }

    /**
     * Cuando se elimina el vínculo (unfollow).
     * Siempre recalculamos por si había rating.
     */
    public function deleted(UserRoutine $userRoutine): void
    {
        $this->recalc($userRoutine);
    }

    /**
     * Si usas soft deletes y se restaura, recalculamos.
     */
    public function restored(UserRoutine $userRoutine): void
    {
        $this->recalc($userRoutine);
    }

    /**
     * Si se fuerza el borrado, recalculamos.
     */
    public function forceDeleted(UserRoutine $userRoutine): void
    {
        $this->recalc($userRoutine);
    }

    /**
     * Recalcular la media de la rutina asociada con seguridad
     * (evita depender de la relación tras delete).
     */
    private function recalc(UserRoutine $userRoutine): void
    {
        $routineId = $userRoutine->routine_id;
        if (!$routineId) return;

        $routine = Routine::find($routineId);
        $routine?->recalcRatingAvg();
    }
}
