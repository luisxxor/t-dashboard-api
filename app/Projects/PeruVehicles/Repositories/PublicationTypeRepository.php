<?php

namespace App\Projects\PeruVehicles\Repositories;

use App\Projects\PeruVehicles\Models\PublicationType;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class PropertyTypeRepository
 * @package App\Projects\PeruVehicles\Repositories
 * @version Ago 22, 2019, 2:19 am UTC
*/
class PublicationTypeRepository
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
        return PublicationType::distinct( $field )->get();
    }
}
