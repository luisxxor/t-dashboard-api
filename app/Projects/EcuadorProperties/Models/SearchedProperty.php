<?php

namespace App\Projects\EcuadorProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class SearchedProperty extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'ecuador_properties';

    /**
     * @var string
     */
    protected $collection = 'searched_properties';

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
