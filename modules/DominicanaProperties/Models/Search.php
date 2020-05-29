<?php

namespace Modules\DominicanaProperties\Models;

use Modules\Common\Models\Search as CommonSearch;

class Search extends CommonSearch
{
    /**
     * @var string
     */
    protected $connection = 'do-properties';

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
