<?php

namespace App\Projects\PeruVehicles\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class SearchedVehicles extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'peru_vehicles';

    /**
     * @var string
     */
    protected $collection = 'searched_vehicles';

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
        'search_id'
    ];
}
