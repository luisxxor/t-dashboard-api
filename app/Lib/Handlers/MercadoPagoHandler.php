<?php

namespace App\Lib\Handlers;

use MercadoPago\Item as MercadoPagoItem;
use MercadoPago\MerchantOrder as MercadoPagoMerchantOrder;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\Preference as MercadoPagoPreference;
use MercadoPago\SDK as MercadoPagoSDK;

class MercadoPagoHandler
{
    /**
     * @var string
     */
    protected $link;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var MercadoPagoPreference
     */
    protected $preference;

    /**
     * Create a new class instance.
     *
     * @param string $accessToken Access Token for SDK.
     *
     * @return void
     */
    public function __construct( string $accessToken )
    {
        MercadoPagoSDK::setAccessToken( $accessToken );
    }

    /**
     * Set preference.
     *
     * @param array $attributes
     * @see https://www.mercadopago.com.pe/developers/es/reference/preferences/resource/
     *
     * @return void
     */
    public function setPreference( array $attributes = [] ): void
    {
        $this->preference = new MercadoPagoPreference();

        // add custom attributes
        foreach ( $attributes as $name => $value ) {
            $this->preference->{ $name } = $value;
        }

        // payment_methods
        $this->preference->payment_methods = [
            'excluded_payment_methods' => [
                [
                    'id' => 'master'
                ]
            ],
            'excluded_payment_types' => [
                [
                    'id' => 'atm'
                ]
            ],
            'installments' => 1
        ];

        // back_urls
        $this->preference->back_urls = [
            'success' => config( 'app.front_url' ) . '?r=' . 'success',
            'failure' => config( 'app.front_url' ) . '?r=' . 'failure',
            'pending' => config( 'app.front_url' ) . '?r=' . 'pending',
        ];
        $this->preference->auto_return = 'approved';
    }

    /**
     * Add an item to preference.
     *
     * @param array $item
     *
     * @return void
     */
    public function addItem( array $itemAttributes ): void
    {
        $itemInstance = new MercadoPagoItem();

        foreach ( $itemAttributes as $name => $value ) {
            $itemInstance->{ $name } = $value;
        }

        $this->items[] = $itemInstance;
    }

    /**
     * Add items to preference.
     *
     * @param array $items
     *
     * @return void
     */
    public function addItems( array $items ): void
    {
        foreach ( $items as $item ) {
            $this->addItem( $item );
        }
    }

    /**
     * Execute preference.
     *
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        $this->preference->items = $this->items;
        $save = $this->preference->save();

        $this->link = $this->preference->init_point;

        return $save;
    }

    /**
     * Get preference.
     *
     * @return array
     */
    public function getPreference(): array
    {
        return $this->preference->getAttributes();
    }

    /**
     * Get init point to access the checkout.
     *
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * Get payment.
     *
     * @param mixed $paymentId
     *
     * @return mixed
     */
    public function getPayment( $paymentId )
    {
        return MercadoPagoPayment::find_by_id( $paymentId );
    }

    /**
     * Get merchant order.
     *
     * @param mixed $orderId
     *
     * @return mixed
     */
    public function getMerchantOrder( $orderId )
    {
        return MercadoPagoMerchantOrder::find_by_id( $orderId );
    }
}
