<?php

namespace App\Projects\EcuadorProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Tracing extends Moloquent
{
    use SoftDeletes;
    
    /**
     * @var string
     */
    protected $connection = 'ecuador_properties';

    /**
     * @var string
     */
    protected $collection = 'tracings';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro_seg',
        'type_operation_id',
        'property_ids',
        'observation',
        'user_id',
    ];
}
