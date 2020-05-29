<?php

namespace Modules\PeruProperties\Models;

use Modules\Common\Models\Region as CommonRegion;

class Region extends CommonRegion
{
    /**
     * @var string
     */
    protected $connection = 'pe-properties';

    /**
     * @var string
     */
    protected $collection = 'regions';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
