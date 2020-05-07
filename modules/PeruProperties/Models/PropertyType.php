<?php

namespace Modules\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PropertyType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_properties';

    /**
     * @var string
     */
    protected $collection = 'property_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
