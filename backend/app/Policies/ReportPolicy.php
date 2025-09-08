<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /** Listar (siempre filtrar por el propio usuario en el controller) */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Ver un reporte concreto: solo el dueño */
    public function view(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }

    /** Crear: cualquier usuario autenticado puede crear su propio reporte */
    public function create(User $user): bool
    {
        return true;
    }

    /** Actualizar: solo el dueño */
    public function update(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }

    /** Borrar: solo el dueño */
    public function delete(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }

    /** No usamos soft deletes ⇒ false por defecto */
    public function restore(User $user, Report $report): bool
    {
        return false;
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return false;
    }
}
