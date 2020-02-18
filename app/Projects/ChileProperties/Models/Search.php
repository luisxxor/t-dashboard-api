<?php

namespace App\Projects\ChileProperties\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Search extends Moloquent
{
    /**
     * @var string
     */
    protected $connection = 'chile_properties';

    /**
     * @var string
     */
    protected $collection = 'searches';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'selected_properties', 'metadata', 'created_at'
    ];
}
