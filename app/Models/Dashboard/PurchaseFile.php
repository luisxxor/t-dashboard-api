<?php

namespace App\Models\Dashboard;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseFile
 * @package App\Models\Dashboard
 * @version February 6, 2019, 3:27 pm UTC
 *
 * @property \App\Models\Dashboard\Purchase purchase
 * @property \Illuminate\Database\Eloquent\Collection roleUser
 * @property \Illuminate\Database\Eloquent\Collection permissionRole
 * @property \Illuminate\Database\Eloquent\Collection permissionUser
 * @property integer purchase_id
 * @property string file_path
 * @property string bucket_name
 * @property string file_name
 * @property integer row_quantity
 * @property string filters
 * @property float amount
 * @property float tax
 */
class PurchaseFile extends Model
{
    use SoftDeletes;

    public $table = 'purchase_files';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];

    protected $appends = [
        'user_id',
        'mp_status',
    ];


    public $fillable = [
        'purchase_id',
        'file_path',
        'bucket_name',
        'file_name',
        'row_quantity',
        'filters',
        'amount',
        'tax'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'purchase_id' => 'integer',
        'file_path' => 'string',
        'bucket_name' => 'string',
        'file_name' => 'string',
        'row_quantity' => 'integer',
        'filters' => 'string',
        'amount' => 'float',
        'tax' => 'float'
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
    public function purchase()
    {
        return $this->belongsTo(\App\Models\Dashboard\Purchase::class);
    }

    /**
     * Get the purchase_id.
     *
     * @return string
     */
    public function getUserIdAttribute()
    {
        $userId = $this->purchase()->first()->user_id;

        return $userId;
    }

    /**
     * Get the purchase_id.
     *
     * @return string
     */
    public function getMpStatusAttribute()
    {
        $MPStatus = $this->purchase()->first()->mp_status;

        return $MPStatus;
    }
}
