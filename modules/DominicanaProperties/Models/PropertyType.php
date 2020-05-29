<?php

namespace Modules\DominicanaProperties\Models;

use Modules\Common\Models\PropertyType as CommonPropertyType;

class PropertyType extends CommonPropertyType
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
