<?php

namespace App\Models\Dashboard;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Purchase
 * @package App\Models\Dashboard
 * @version February 5, 2019, 4:16 am UTC
 *
 * @property \App\Models\Dashboard\User user
 * @property \Illuminate\Database\Eloquent\Collection roleUser
 * @property \Illuminate\Database\Eloquent\Collection permissionRole
 * @property \Illuminate\Database\Eloquent\Collection PurchaseFile
 * @property \Illuminate\Database\Eloquent\Collection permissionUser
 * @property string code
 * @property integer user_id
 * @property string search_id
 * @property float total_amount
 * @property float total_tax
 * @property string status
 * @property string mp_init_point
 * @property string mp_notification_id
 * @property string mp_status
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
        'project',
        // 'total_amount',
        // 'total_tax',
        'status',

        'search_id',
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
        'mp_init_point' => 'string',
        'mp_notification_id' => 'string',
        'mp_status' => 'string',

        'search_id' => 'string',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function purchaseFiles()
    {
        return $this->hasMany(\App\Models\Dashboard\PurchaseFile::class);
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
