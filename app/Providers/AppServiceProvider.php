<?php

namespace App\Providers;

use App\Billing\MercadopagoPaymentGateway;
use App\Billing\NotSupportedPaymentGateway;
use App\Billing\PaymentGatewayContract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength( 191 );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton( PaymentGatewayContract::class, function ( $app ) {
            switch ( request()->get( 'paymentType' ) ) {
                case config( 'constants.payment_gateways.MERCADOPAGO' ):

                    return new MercadopagoPaymentGateway( request()->get( 'currency' ) );
                    break;

                default:

                    return new NotSupportedPaymentGateway();
                    break;
            }
        } );
    }
}
