<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Receipt;
use App\Repositories\BaseRepository;

/**
 * Class ReceiptRepository
 * @package App\Repositories\Dashboard
 * @version April 1, 2020, 15:19 UTC
*/
class ReceiptRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'receiptable_type',
        'receiptable_id',
        'currency',
        'payment_type',
        'payment_info',
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
     * @return Receipt
     */
    public function model()
    {
        return Receipt::class;
    }

    /**
     * Find or create receipt record with code.
     *
     * @param \Illuminate\Database\Eloquent\Model $receiptable
     * @throws \Exception
     *
     * @return \App\Models\Dashboard\Receipt
     */
    public function findOrCreateByReceiptable( $receiptable )
    {
        if ( method_exists( $receiptable, 'receipt' ) === false ) {
            throw new \Exception( 'you can only create a receipt to a model that implements App\Traits\Dashboard\HasReceipt trait.' );
        }

        // if already has receipt
        if ( $receiptable->receipt !== null ) {
            return $receiptable->receipt;
        }

        // create receipt
        $receipt = $receiptable->receipt()->create( [] );

        // create code
        $receipt->code = $receipt->id;
        $receipt->save();

        return $receipt;
    }
}
