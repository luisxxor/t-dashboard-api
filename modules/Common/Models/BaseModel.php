<?php

namespace Modules\Common\Models;

use DateTimeInterface;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class BaseModel extends Moloquent
{
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate( DateTimeInterface $date )
    {
        return $date->format( config( 'app.datetime_format' ) );
    }
}