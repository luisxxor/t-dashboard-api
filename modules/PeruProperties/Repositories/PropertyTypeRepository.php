<?php

namespace Modules\PeruProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\PeruProperties\Models\PropertyType;

/**
 * Class PropertyTypeRepository
 * @package Modules\PeruProperties\Repositories
 * @version Ago 22, 2019, 06:19 UTC
*/
class PropertyTypeRepository extends Repository
{
    /**
     * Configure the Model
     *
     * @return string
     */
    public function model()
    {
        return PropertyType::class;
    }
}
