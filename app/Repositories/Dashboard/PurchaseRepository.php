<?php

namespace App\Repositories\Dashboard;

use App\Lib\Handlers\MercadoPagoItem;
use App\Lib\Handlers\MercadoPagoPreference;
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
        'mp_status'
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
     **/
    public function model()
    {
        return Purchase::class;
    }

    /**
     * Generate purchase.
     **/
    public function generate( array $attributes )
    {
        // create the Purchase
        $purchase = $this->create( $attributes );

        // set the code (it has a mutator) of the Purchase
        $purchase->code = $purchase->id;

        // instance the MercadoPago Preference
        $preferenceMP = new MercadoPagoPreference();

        // set the external_reference value (code of the Purchase) to the MercadoPago Preference
        $preferenceMP->setExternalReference( $purchase->code );

        // iterate files
        $items = [];
        $total = 0;
        foreach ( $attributes[ 'files' ] as $file ) {
            // set the PurchaseFile.
            $purchaseFile = $this->setPurchaseFile( $purchase, $file );

            // sum the total amount
            $total += $purchaseFile->amount;

            // create item (MercadoPago)
            $items[] = $this->setMercadoPagoItem( $purchaseFile );
        }

        // Add items to MercadoPago Preference
        $preferenceMP->addItems( $items );

        // Save MercadoPago Preference
        $preferenceMP->save();

        // get MercadoPago Preference init point
        $purchase->mp_init_point = $preferenceMP->getLink();

        // save the total amount
        $purchase->total_amount = $total;

        $purchase->save();

        return $purchase;
    }

    /**
     * set the PurchaseFile.
     **/
    protected function setPurchaseFile( $purchase, array $file )
    {
        // PurchaseFile data
        $purchaseFileData = [];
        // $purchaseFileData[ 'file_path' ]        = $file[ 'file_path' ];
        // $purchaseFileData[ 'file_name' ]        = $file[ 'file_name' ];
        // $purchaseFileData[ 'filters' ]          = '';
        $purchaseFileData[ 'row_quantity' ] = $file[ 'row_quantity' ];

        $purchaseFileData[ 'amount' ]       = $this->calculateAmount( $file[ 'row_quantity' ] );
        $purchaseFileData[ 'tax' ]          = 0.00;

        // create PurchaseFile
        return $purchase->purchaseFiles()->create( $purchaseFileData );
    }

    /**
     * Calculate the amount with the rows quantity.
     **/
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
     * set the MercadoPago item.
     **/
    protected function setMercadoPagoItem( $purchaseFile )
    {
        // add items to preferenceMP
        $mercadoPagoItem = new MercadoPagoItem( [
            'id'            => $purchaseFile->id,
            'title'         => 'Compra de registros Tasing!',
            'quantity'      => 1,
            'currency_id'   => 'PEN',
            'unit_price'    => $purchaseFile->amount
        ] );

        return $mercadoPagoItem->getItemInstance();
    }
}
