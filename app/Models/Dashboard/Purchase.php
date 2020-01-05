<?php

namespace App\Models\Dashboard;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Purchase
 * @package App\Models\Dashboard
 * @version February 5, 2019, 4:16 am UTC
 */
class Purchase extends Model
{
    use SoftDeletes;

    public $table = 'purchases';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'search_id',
        'project',
        'total_rows_quantity',
        'payment_type',
        'currency',
        'status',

        'payment_info->payment',
    ];

    protected $hidden = [
        'payment_info',
        'files_info',
        'updated_at',
        'deleted_at',
        'search_id',
        'payment_type',
        'currency',
        'project',
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
        'total_amount' => 'float',
        'total_tax' => 'float',
        'status' => 'string',

        'search_id' => 'string',
        'project' => 'string',
        'currency' => 'string',
        'payment_type' => 'string',
        'payment_info' => 'array',
        'files_info' => 'array',
        'total_rows_quantity' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\Dashboard\User::class);
    }

    /**
     * Set the code.
     *
     * @param  int  $value
     * @return void
     */
    public function setCodeAttribute( $value )
    {
        $this->attributes[ 'code' ] = 'purchase-' . str_pad( $value, 8, '0', STR_PAD_LEFT );
    }
}
