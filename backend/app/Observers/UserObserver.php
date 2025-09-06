<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (! $user->profile()->exists()) {
            $user->profile()->firstOrCreate([], [
            'bio'         => null,
            'sport'       => null,
            'birthdate'   => null,
            'avatar_b64'  => null,
            'avatar_mime' => null,
            'avatar_size' => null,
        ]);
        }
    }
}
