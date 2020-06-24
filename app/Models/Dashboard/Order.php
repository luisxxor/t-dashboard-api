<?php

namespace App\Models\Dashboard;

use App\Models\BaseModel;
use App\Traits\Dashboard\HasReceipt;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Order
 * @package App\Models\Dashboard
 * @version February 5, 2019, 00:16 UTC
 */
class Order extends BaseModel
{
    use SoftDeletes, HasReceipt;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'orders';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [ 'deleted_at' ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'search_id',
        'project',
        'total_rows_quantity',
        'status',
        'metadata_info',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
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
    public static $rules = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'init_point_address',
        'status_label',
    ];

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
    public function getStatusLabelAttribute()
    {
        return config( 'constants.STATUS_LABELS.' . $this->status, $this->status );
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
