<?php

namespace App\Projects\EcuadorProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Region extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'ecuador_properties';

    /**
     * @var string
     */
    protected $collection = 'regions';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
