<?php

namespace App\Lib\Handlers;

use Illuminate\Support\Facades\Config;
use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\SDK as MercadoPagoSDK;

class MercadoPagoPreference
{
    protected $link;
    protected $testLink;
    protected $items;
    protected $preference;

    public function __construct()
    {
        // MercadoPagoSDK::setAccessToken( 'TEST-4327864427102266-011223-227765a02b168472dc2605f952a1314c-394556774' );
        MercadoPagoSDK::setClientId( '5002297790161278' );
        MercadoPagoSDK::setClientSecret( 'hD2YzCIiL3uAAI4GuPlrLxkFo35DLY46' );

        $this->preference = new Preference();

        // test payment_methods
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
            // 'installments' => 12
        ];

        // test back_urls
        $this->preference->back_urls = [
            'success' => Config::get( 'app.front_url' ) . '?r=' . 'success',
            'failure' => Config::get( 'app.front_url' ) . '?r=' . 'failure',
            'pending' => Config::get( 'app.front_url' ) . '?r=' . 'pending',
        ];
        $this->preference->auto_return = 'approved';
        // http://localhost/f/tasing/dashboard/public_html/properties?r=success&collection_id=4586796180&collection_status=approved&preference_id=415292930-c5638729-492e-4659-9a48-d2a77a4ac81e&external_reference=purchase-00000029&payment_type=debit_card&merchant_order_id=988244062
    }

    public function setExternalReference( string $externalReference ): void
    {
        $this->preference->external_reference = $externalReference;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getTestLink(): string
    {
        return $this->testLink;
    }

    public function addItem( Item $item ): void
    {
        $this->items[] = $item;
    }

    public function addItems( array $items ): void
    {
        foreach ( $items as $item ) {
            $this->addItem( $item );
        }
    }

    public function save(): void
    {
        $this->preference->items = $this->items;
        $save = $this->preference->save();

        $this->link     = $this->preference->init_point;
        $this->testLink = $this->preference->sandbox_init_point;
    }
}
