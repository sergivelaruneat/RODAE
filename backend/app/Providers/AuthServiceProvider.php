<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Gate;

use App\Models\Publication;
use App\Policies\PublicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [ \App\Models\Publication::class => \App\Policies\PublicationPolicy::class ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Personalizar URL de reset password → frontend
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $front = config('app.frontend_url');
            $email = urlencode($user->email);
            return "{$front}/resetpassword?token={$token}&email={$email}";
        });
    }
}

