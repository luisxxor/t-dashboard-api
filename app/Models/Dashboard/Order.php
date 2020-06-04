<?php

namespace App\Models\Dashboard;

use App\Traits\Dashboard\HasReceipt;
use Eloquent as Model;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Order
 * @package App\Models\Dashboard
 * @version February 5, 2019, 4:16 am UTC
 */
class Order extends Model
{
    use SoftDeletes, HasReceipt;

    public $table = 'orders';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'user_id',
        'search_id',
        'project',
        'total_rows_quantity',
        'status',
        'metadata_info',
    ];

    protected $hidden = [
        'user_id',
        'search_id',
        'project',
        'files_info',
        'metadata_info',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'user_id' => 'integer',
        'status' => 'string',
        'search_id' => 'string',
        'project' => 'string',
        'files_info' => 'array',
        'metadata_info' => 'array',
        'total_rows_quantity' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    protected $appends = [ 'init_point_address' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo( \App\Models\Dashboard\User::class );
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

        $this->attributes[ 'code' ] = $prefix . 'orden-' . str_pad( $value, 8, '0', STR_PAD_LEFT );
    }

    /**
     * Get the init point address.
     *
     * @return string
     */
    public function getInitPointAddressAttribute(): string
    {
        return $this->metadata_info[ 'initPoint' ][ 'address' ] ?? '';
    }

    /**
     * Get the status translation.
     *
     * @param  string  $value
     * @return string
     */
    public function getStatusAttribute( $value ): string
    {
        return config( 'constants.STATUS_LABELS.' . $value, $value );
    }

    /**
     * Set 'pending' status in Order.
     *
     * @return \App\Models\Dashboard\Order
     */
    public function setPendingStatus()
    {
        $this->status = config( 'constants.ORDERS.STATUS.PENDING' );
        $this->save();

        return $this;
    }

    /**
     * Set 'released' status in Order.
     *
     * @return \App\Models\Dashboard\Order
     */
    public function setReleasedStatus()
    {
        // generate files request
        $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
        $guzzleClient->sendAsync( new GuzzleRequest(
            'GET',
            route( 'api.' . config( 'multi-api.' . $this->project . '.backend-info.generate_file_url' ), [], false ),
            [ 'Content-type' => 'application/json' ],
            json_encode( [ 'orderCode' => $this->code ] )
        ) )->wait( false );

        // release item.
        $this->status = config( 'constants.ORDERS.STATUS.RELEASED' );
        $this->save();

        return $this;
    }
}
