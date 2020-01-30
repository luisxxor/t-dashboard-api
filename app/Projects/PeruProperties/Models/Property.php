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
     * @var string
     */
    protected $fillable = array('*');

    /**
     * @var array
     */
    protected $dates = [
        'created_at', 
        'updated_at', 
        'deleted_at', 
        'publication_date'
    ];
}
