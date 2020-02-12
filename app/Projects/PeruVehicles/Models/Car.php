<?php

namespace App\Projects\PeruVehicles\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Car extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_vehicles';

    /**
     * @var string
     */
    protected $collection = 'cars';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * @var array
     */
    protected $dates = [ 'publication_date' ];
}
