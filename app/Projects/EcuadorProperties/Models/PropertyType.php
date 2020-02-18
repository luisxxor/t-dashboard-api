<?php

namespace App\Projects\EcuadorProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PropertyType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'ecuador_properties';

    /**
     * @var string
     */
    protected $collection = 'property_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
