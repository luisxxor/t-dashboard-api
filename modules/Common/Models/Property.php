<?php

namespace Modules\Common\Models;

use DateTime;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Property extends Moloquent
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setDateFormat( config( 'app.datetime_format' ) );
    }
}