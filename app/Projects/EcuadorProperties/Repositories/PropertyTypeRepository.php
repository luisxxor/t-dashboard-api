<?php

namespace App\Projects\EcuadorProperties\Repositories;

use App\Projects\EcuadorProperties\Models\PropertyType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PropertyTypeRepository
 * @package App\Projects\EcuadorProperties\Repositories
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
        return PropertyType::distinct( 'name' )->get();
    }
}
