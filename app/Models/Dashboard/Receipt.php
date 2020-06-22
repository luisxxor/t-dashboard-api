<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;

class Receipt extends BaseModel
{
    public $table = 'receipts';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at', 'deleted_at', 'payment_info', 'payment_type', 'currency',
    ];

    public $fillable = [
        'payment_info->payment',
        'status',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'code' => 'string',
        'total_amount' => 'float',
        'total_tax' => 'float',
        'currency' => 'string',
        'payment_type' => 'string',
        'payment_info' => 'array',
        'status' => 'string',
    ];

    /**
     * Get the owning of the receipt.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function receiptable()
    {
        return $this->morphTo( 'receiptable' );
    }

    /**
     * Set the code.
     *
     * @param  int  $value
     * @return void
     */
    public function setCodeAttribute( $value )
    {
        $prefix = config( 'app.env' ) !== 'production' ? 'dev.' : '';

        $this->attributes[ 'code' ] = $prefix . 'receipt-' . str_pad( $value, 8, '0', STR_PAD_LEFT );
    }

    /**
     * Check if the receipt has released status.
     *
     * @return bool
     */
    public function isReleasedStatus(): bool
    {
        return $this->status === config( 'constants.RECEIPTS.STATUS.RELEASED' );
    }

    /**
     * Set 'pending' status in receipt and receiptable.
     *
     * @return \App\Models\Dashboard\Receipt
     */
    public function setPendingStatus()
    {
        // receipt status
        $this->status = config( 'constants.RECEIPTS.STATUS.PENDING' );
        $this->save();

        // receiptable status
        $this->receiptable->setPendingStatus();

        return $this;
    }

    /**
     * Set 'released' status in receipt and receiptable.
     *
     * @return \App\Models\Dashboard\Receipt
     */
    public function setReleasedStatus()
    {
        // receipt status
        $this->status = config( 'constants.RECEIPTS.STATUS.RELEASED' );
        $this->save();

        // receiptable status
        $this->receiptable->setReleasedStatus();

        return $this;
    }
}
