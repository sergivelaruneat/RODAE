<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    /**
     * Solo el dueño puede borrar su publicación
     */
    public function delete(User $user, Publication $publication): bool
    {
        return $publication->user_id === $user->id;
    }
}

