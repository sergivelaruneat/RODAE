<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;

class RoutinePolicy
{
    // Listar y ver: público (no exige auth)
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Routine $routine): bool
    {
        return true;
    }

    // Crear: solo entrenadores
    public function create(User $user): bool
    {
        return $user->role === 'trainer';
    }

    // Actualizar/Eliminar: solo el entrenador propietario
    public function update(User $user, Routine $routine): bool
    {
        return $user->role === 'trainer' && (int)$routine->owner_user_id === (int)$user->id;
    }

    public function delete(User $user, Routine $routine): bool
    {
        return $user->role === 'trainer' && (int)$routine->owner_user_id === (int)$user->id;
    }

    // Asignar rutina a un usuario: solo el entrenador propietario
    public function assignToUser(User $user, Routine $routine): bool
    {
        return $user->role === 'trainer' && (int)$routine->owner_user_id === (int)$user->id;
    }

}
