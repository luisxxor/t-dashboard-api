<?php

namespace App\Projects\TracingProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Tracing extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'tracing';

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
