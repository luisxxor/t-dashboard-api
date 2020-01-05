<?php

namespace App\Repositories\Dashboard;

use App\Lib\Handlers\MercadoPagoHandler;
use App\Models\Dashboard\Purchase;
use App\Repositories\BaseRepository;

/**
 * Class PurchaseRepository
 * @package App\Repositories\Dashboard
 * @version February 5, 2019, 4:16 am UTC
*/
class PurchaseRepository extends BaseRepository
{
    /** @const float */
    const BASE_PRICE = 25.0;

    /** @const int */
    const BASE_QUANTITY = 15;

    /** @const float */
    const ADDITIONAL_PRICE = 0.5;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'user_id',
        'total_amount',
        'total_tax',
        'status',
        'mp_init_point',
        'mp_notification_id',
        'mp_status',

        'search_id',
        'project',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     *
     * @return Purchase
     */
    public function model()
    {
        return Purchase::class;
    }

    /**
     * Calculate the amount with the rows quantity.
     *
     * @return float
     */
    protected function calculateAmount( int $rowQuantity ): float
    {
        $amount = self::BASE_PRICE;

        // si la catidad de registros es mayor a la base de precio fijo
        if ( $rowQuantity > self::BASE_QUANTITY ) {

            // obtengo la cantidad adicional
            $additionalQuantity = $rowQuantity - self::BASE_QUANTITY;

            // sumo el precio adicional
            $amount += $additionalQuantity * self::ADDITIONAL_PRICE;
        }

        return $amount;
    }

    /**
     * Create purchase, and process it with given payment method.
     *
     * @return Purchase
     * @throws \Exception
     */
    public function process( array $purchaseAttributes )
    {
        // create the Purchase
        $purchase = $this->create( $purchaseAttributes );

        // set the code (it has a mutator) of the Purchase
        $purchase->code = $purchase->id;

        $paymentProcess = null;

        switch ( $purchase->payment_type ) {
            case config( 'constants.PAYMENTS_MERCADOPAGO' ):

                $paymentProcess = $this->processWithMercadopago( $purchase );
                break;

            default:

                throw new \Exception( 'Payment method not supported.' );
                break;
        }

        // save the amounts
        $purchase->total_amount = $paymentProcess[ 'total_amount' ];
        $purchase->total_tax = 0.0;

        // save payment info
        $purchase->payment_info = $paymentProcess;

        $purchase->save();

        return $purchase;
    }

    /**
     * Process payment with Mercadopago.
     *
     * @return array
     * @throws \Exception
     */
    protected function processWithMercadopago( Purchase $purchase ): array
    {
        $mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );

        // set preference with external reference
        $mercadoPago->setPreference( [ 'external_reference' => $purchase->code ] );

        // create item to process purchase
        $item = [
            // 'id'            => 'aqui iria el id, si lo tuviera',
            'title'         => 'Información de ' . $purchase->total_rows_quantity . ' registros de Tasing!',
            'quantity'      => 1,
            'currency_id'   => $purchase->currency,
            'unit_price'    => $this->calculateAmount( $purchase->total_rows_quantity ),
        ];

        // add item to MercadoPago Preference
        $mercadoPago->addItem( $item );

        // execute MercadoPago Preference
        $mercadoPago->save();

        return [
            'total_amount' => $item[ 'unit_price' ],
            'init_point' => $mercadoPago->getLink(),
            'preference' => $mercadoPago->getPreference(),
        ];
    }
}
