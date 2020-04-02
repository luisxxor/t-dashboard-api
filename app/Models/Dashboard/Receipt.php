<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
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

    public function isReleasedStatus(): bool
    {
        return $this->status === config( 'constants.ORDERS_RELEASED_STATUS' );
    }

    public function setPendingStatus()
    {
        $this->status = config( 'constants.RECEIPTS.STATUS.PENDING' );
        $this->save();

        /// order
        // $order->status = config( 'constants.ORDERS_PENDING_STATUS' );
        // $order->save();
    }

    public function setReleasedStatus()
    {
        $this->status = config( 'constants.RECEIPTS.STATUS.PENDING' );
        $this->save();

        /// order
        // // generate files request
        // $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
        // $guzzleClient->sendAsync( new GuzzleRequest(
        //     'GET',
        //     route( 'api.' . config( 'multi-api.' . $order->project . '.backend-info.generate_file_url' ), [], false ),
        //     [ 'Content-type' => 'application/json' ],
        //     json_encode( [ 'orderCode' => $order->code ] )
        // ) )->wait( false );
        //
        // // release item.
        // $order->status = config( 'constants.ORDERS_RELEASED_STATUS' );
        // $order->save();
    }
}
