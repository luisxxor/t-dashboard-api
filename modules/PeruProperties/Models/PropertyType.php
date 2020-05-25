<?php

namespace Modules\PeruProperties\Models;

use Modules\Common\Models\PropertyType as CommonPropertyType;

class PropertyType extends CommonPropertyType
{
    /**
     * @var string
     */
    protected $connection = 'pe-properties';

    /**
     * @var string
     */
    protected $collection = 'property_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
