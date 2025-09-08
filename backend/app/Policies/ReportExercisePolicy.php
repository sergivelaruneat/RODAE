<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\ReportExercise;
use App\Models\User;

class ReportExercisePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReportExercise $item): bool
    {
        return $item->report && $item->report->user_id === $user->id;
    }

    /**
     * Crear item para un Report concreto.
     * Para usarlo: Gate::authorize('create', [ReportExercise::class, $report]);
     */
    public function create(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function update(User $user, ReportExercise $item): bool
    {
        return $item->report && $item->report->user_id === $user->id;
    }

    public function delete(User $user, ReportExercise $item): bool
    {
        return $item->report && $item->report->user_id === $user->id;
    }

    public function restore(User $user, ReportExercise $item): bool
    {
        return false;
    }

    public function forceDelete(User $user, ReportExercise $item): bool
    {
        return false;
    }
}
