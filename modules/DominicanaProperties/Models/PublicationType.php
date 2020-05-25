<?php

namespace Modules\DominicanaProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class PublicationType extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'do-properties';

    /**
     * @var string
     */
    protected $collection = 'publication_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
