<?php

namespace App\Projects\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class TypeOperation extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'tracing';

    /**
     * @var string
     */
    protected $collection = 'type_operations';

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
        'type_operation',
        'code'
    ];
}
