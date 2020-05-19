<?php

namespace Modules\DominicanaProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Region extends Moloquent
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
