<?php

namespace App\Projects\PeruVehicles\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PublicationType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_vehicles';

    /**
     * @var string
     */
    protected $collection = 'publication_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
