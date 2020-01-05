<?php

namespace App\Projects\PeruProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Property extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_properties';

    /**
     * @var string
     */
    protected $collection = 'properties';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * @var array
     */
    protected $dates = [ 'publication_date' ];
}
