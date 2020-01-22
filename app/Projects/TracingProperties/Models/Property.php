<?php

namespace App\Projects\TracingProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Property extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'tracing';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'property_type_id',
        'bathrooms',
        'bedrooms',
        'build_area_m2',
        'comment_description',
        'comment_subtitle',
        'created_at',
        'dollars_price',
        'latitude',
        'longitude',
        'property_name',
        'property_new',
        'geo_location',
        'client_id',
        'property_id'
    ];
}
