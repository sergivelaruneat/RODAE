<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserRoutine;
use App\Models\RoutineExercise;

use App\Observers\UserObserver;
use App\Observers\UserRoutineObserver;
use App\Observers\RoutineExerciseObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        UserRoutine::observe(UserRoutineObserver::class);
        RoutineExercise::observe(RoutineExerciseObserver::class);
    }
}
