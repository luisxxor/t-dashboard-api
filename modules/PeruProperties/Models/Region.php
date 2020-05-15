<?php

namespace Modules\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Region extends Moloquent
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
