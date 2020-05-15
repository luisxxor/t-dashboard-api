<?php

namespace App\Billing;

use App\Billing\MercadopagoPaymentGateway;
use App\Billing\NotSupportedPaymentGateway;
use App\Billing\PaymentGatewayContract;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class PaymentGatewayServiceProvider extends ServiceProvider implements DeferrableProvider
{
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

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ PaymentGatewayContract::class ];
    }
}
