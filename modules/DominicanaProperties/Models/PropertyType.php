<?php

namespace Modules\DominicanaProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PropertyType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'do-properties';

    /**
     * @var string
     */
    protected $collection = 'property_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
