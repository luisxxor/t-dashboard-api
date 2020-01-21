<?php

namespace App\Billing;

use App\Lib\Handlers\MercadoPagoHandler;

class MercadopagoPaymentGateway implements PaymentGatewayContract
{
    private $currency;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct( $currency )
    {
        $this->currency = $currency;
    }

    /**
     * Charge payment with Mercadopago.
     *
     * @param string $orderCode
     * @param float $amount
     * @param int $itemsQuantity
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $orderCode, float $amount, int $itemsQuantity ): array
    {
        $mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );

        // set preference with external reference
        $mercadoPago->setPreference( [ 'external_reference' => $orderCode ] );

        // create item to process order
        $item = [
            // 'id'            => 'aqui iria el id, si lo tuviera',
            'title'         => 'Información de ' . $itemsQuantity . ' registros de Tasing!',
            'quantity'      => 1,
            'currency_id'   => $this->currency,
            'unit_price'    => $amount,
        ];

        // add item to MercadoPago Preference
        $mercadoPago->addItem( $item );

        // execute MercadoPago Preference
        $mercadoPago->save();

        return [
            'total_amount' => $amount,
            'init_point' => $mercadoPago->getLink(),
            'preference' => $mercadoPago->getPreference(),
        ];
    }
}