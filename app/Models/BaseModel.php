<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Format created_at field.
     *
     * @return string
     */
    protected function getCreatedAtAttribute()
    {
        if ( DateTime::createFromFormat( $this->dateFormat, $this->attributes[ 'created_at' ] ) === false ) {
            dd( $this->dateFormat, $this->attributes[ 'created_at' ] );
        }
        return DateTime::createFromFormat( $this->dateFormat, $this->attributes[ 'created_at' ] )->format( config( 'app.datetime_format' ) );
    }

    /**
     * Format created_at field.
     *
     * @return string
     */
    protected function getUpdatedAtAttribute()
    {
        return DateTime::createFromFormat( $this->dateFormat, $this->attributes[ 'updated_at' ] )->format( config( 'app.datetime_format' ) );
    }
}