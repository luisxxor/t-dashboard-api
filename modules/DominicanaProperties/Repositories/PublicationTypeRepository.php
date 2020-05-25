<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\DominicanaProperties\Models\PublicationType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PublicationTypeRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 25, 2020, 04:58 UTC
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
