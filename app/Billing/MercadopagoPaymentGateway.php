<?php

namespace App\Billing;

use App\Lib\Handlers\MercadoPagoHandler;

class MercadopagoPaymentGateway implements PaymentGatewayContract
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @var \App\Lib\Handlers\MercadoPagoHandler
     */
    private $mercadoPago;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct( $currency )
    {
        $this->currency = $currency;
        $this->mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );
    }

    /**
     * Charge payment with Mercadopago.
     *
     * @param string $receiptCode
     * @param float $amount
     * @param array $options {
     *     Configuration options.
     *
     *     @type string $title [optional] Title of item being charged.
     * }
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $receiptCode, float $amount, array $options = [] ): array
    {
        $opt[ 'title' ] = 'Descarga de registros de Tasing!';

        foreach ( $options as $optionName => $o ) {
            $opt[ $optionName ] = $o;
        }

        // set preference with external reference
        $this->mercadoPago->setPreference( [ 'external_reference' => $receiptCode ] );

        // create item to process order
        $item = [
            // 'id'            => 'aqui iria el id, si lo tuviera',
            'title'         => $opt[ 'title' ],
            'quantity'      => 1,
            'currency_id'   => $this->currency,
            'unit_price'    => $amount,
        ];

        // add item to MercadoPago Preference
        $this->mercadoPago->addItem( $item );

        // execute MercadoPago Preference
        $this->mercadoPago->save();

        return [
            'total_amount' => $amount,
            'init_point' => $this->mercadoPago->getLink(),
            'preference' => $this->mercadoPago->getPreference(),
        ];
    }
}