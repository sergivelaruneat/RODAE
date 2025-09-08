<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportPolicy
{
    /**
     * Cualquiera autenticado puede usar los endpoints de listado/consulta
     * (el filtro de user_id y la mutualidad se comprueban en el controller
     * cuando corresponde). Mantener simple.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver un reporte concreto:
     * - dueñ@ del reporte
     * - o entrenador con follow mutuo con el atleta dueño del reporte
     */
    public function view(User $user, Report $report): bool
    {
        if ($user->id === (int) $report->user_id) {
            return true;
        }

        return $this->trainerHasMutualFollowWith($user, (int) $report->user_id);
    }

    /**
     * Crear: cualquier usuario autenticado.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Editar/borrar: únicamente el dueño del reporte.
     */
    public function update(User $user, Report $report): bool
    {
        return $user->id === (int) $report->user_id;
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->id === (int) $report->user_id;
    }

    /**
     * Helper: ¿el usuario (trainer) y el atleta tienen follow mutuo?
     */
    private function trainerHasMutualFollowWith(User $maybeTrainer, int $athleteId): bool
    {
        if ($maybeTrainer->role !== 'trainer') {
            return false;
        }

        // trainer -> atleta
        $a = DB::table('follows')
            ->where('follower_id', $maybeTrainer->id)
            ->where('followed_id', $athleteId)
            ->exists();

        // atleta -> trainer
        $b = DB::table('follows')
            ->where('follower_id', $athleteId)
            ->where('followed_id', $maybeTrainer->id)
            ->exists();

        return $a && $b;
    }
}
