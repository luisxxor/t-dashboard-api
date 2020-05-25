<?php

namespace Modules\DominicanaProperties\Models;

use Modules\Common\Models\Region as CommonRegion;

class Region extends CommonRegion
{
    /**
     * @var string
     */
    protected $connection = 'do-properties';

    /**
     * @var string
     */
    protected $collection = 'regions';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
