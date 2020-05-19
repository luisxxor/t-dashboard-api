<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        // tokens expires in an hour
        Passport::tokensExpireIn( now()->addHour() );
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        Passport::tokensCan( [
            'access-pe-properties' => 'Acceder a Propiedades Perú',
            'access-cl-properties' => 'Acceder a Propiedades Chile',
            'access-ec-properties' => 'Acceder a Propiedades Ecuador',
            'access-do-properties' => 'Acceder a Propiedades Dominicana',
        ] );
    }
}
