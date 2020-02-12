<?php

namespace App\Projects\PeruVehicles\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Region extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_vehicles';

    /**
     * @var string
     */
    protected $collection = 'regions';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
