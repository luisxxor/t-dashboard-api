<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\DominicanaProperties\Models\PropertyType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PropertyTypeRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version Ago 22, 2019, 2:19 am UTC
*/
class PropertyTypeRepository
{
    /**
     * Save a new document in collection
     *
     * @param array $attributes
     *
     * @return string
     */
    public function distinct( string $field ): Collection
    {
        return PropertyType::distinct( 'owner_name' )->get();
    }
}
