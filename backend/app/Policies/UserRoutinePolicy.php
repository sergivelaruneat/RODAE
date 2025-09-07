<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserRoutine;

class UserRoutinePolicy
{
    /**
     * Listar vínculos (no la usamos normalmente, pero OK).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver un vínculo user↔routine: solo su dueño.
     */
    public function view(User $user, UserRoutine $userRoutine): bool
    {
        return (int) $user->id === (int) $userRoutine->user_id;
    }

    /**
     * Seguir una rutina: cualquier usuario autenticado.
     * (Se autoriza sobre la CLASE: authorize('follow', UserRoutine::class))
     */
    public function follow(User $user): bool
    {
        return true;
    }

    /**
     * Dejar de seguir: el dueño del vínculo
     * o el entrenador propietario de la rutina (para "desasignar").
     */
    public function unfollow(User $user, UserRoutine $userRoutine): bool
    {
        return (int) $user->id === (int) $userRoutine->user_id
            || (
                $user->role === 'trainer'
                && $userRoutine->routine
                && (int) $userRoutine->routine->owner_user_id === (int) $user->id
            );
    }

    /**
     * Valorar una rutina: solo el dueño del vínculo.
     */
    public function rate(User $user, UserRoutine $userRoutine): bool
    {
        return (int) $user->id === (int) $userRoutine->user_id;
    }

    /**
     * Borrar el vínculo: misma regla que unfollow.
     */
    public function delete(User $user, UserRoutine $userRoutine): bool
    {
        return $this->unfollow($user, $userRoutine);
    }
}
