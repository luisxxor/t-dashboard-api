<?php

namespace Modules\PeruProperties\Models;

use Modules\Common\Models\Property as CommonProperty;

class Property extends CommonProperty
{
    /**
     * @var string
     */
    protected $connection = 'pe-properties';

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
