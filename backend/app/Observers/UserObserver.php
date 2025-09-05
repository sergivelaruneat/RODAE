<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (! $user->profile()->exists()) {
            $user->profile()->create([
                // ajusta nombres de columnas a tu tabla `profiles`
                'avatar_path' => null,
                'sport_main'  => null,
                'birthdate'   => null,
                'bio'         => null,
            ]);
        }
    }
}
