<?php

namespace App\Routers;

use Illuminate\Support\Facades\Route;

class AuthAPI
{
    /**
     * Register the typical authentication routes for an application.
     *
     * @param  array  $options
     * @return void
     */
    public static function routes( array $options = [] )
    {
        // Authentication Routes...
        Route::post( 'login', 'API\Auth\LoginAPIController@login' );

        // Registration Routes...
        if ( $options[ 'register' ] ?? true ) {
            Route::post( 'register', 'API\Auth\RegisterAPIController@register' );
        }

        // Password Reset Routes...
        if ( $options[ 'reset' ] ?? true ) {
            static::resetPassword();
        }

        // Email Verification Routes...
        if ( $options[ 'verify' ] ?? false ) {
            static::emailVerification();
        }
    }

    /**
     * Register the typical reset password routes for an application.
     *
     * @return void
     */
    public static function resetPassword()
    {
        Route::post( 'password/email', 'API\Auth\ForgotPasswordAPIController@sendResetLinkEmail' )->name( 'password.email' );
        Route::post( 'password/reset', 'API\Auth\ResetPasswordAPIController@reset' )->name( 'password.update' );
    }

    /**
     * Register the typical email verification routes for an application.
     *
     * @return void
     */
    public static function emailVerification()
    {
        Route::get( 'email/verify', 'API\Auth\VerificationAPIController@show' )->name( 'verification.notice' );
        Route::get( 'email/verify/{id}/{hash}', 'API\Auth\VerificationAPIController@verify' )->name( 'verification.verify' );
        Route::post( 'email/resend', 'API\Auth\VerificationAPIController@resend' )->name( 'verification.resend' );
    }
}