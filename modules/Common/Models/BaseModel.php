<?php

namespace Modules\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class BaseModel extends Moloquent
{
    /**
     * Create a new class instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct( array $attributes = [] )
    {
        parent::__construct( $attributes );
        $this->setDateFormat( config( 'app.datetime_format' ) );
    }
}