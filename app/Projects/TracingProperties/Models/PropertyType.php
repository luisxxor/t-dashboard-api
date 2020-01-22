<?php

namespace App\Projects\TracingProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PropertyType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'tracing';

    /**
     * @var string
     */
    protected $collection = 'property_types';

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
        'item',
        'name'
    ];
}
