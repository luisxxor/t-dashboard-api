<?php

namespace Modules\PeruProperties\Models;

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
     * @var string
     */
    protected $keyType = 'int';

    /**
     * @var array
     */
    protected $dates = [ 'publication_date' ];
}
