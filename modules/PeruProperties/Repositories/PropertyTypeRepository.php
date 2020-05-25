<?php

namespace Modules\PeruProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\PeruProperties\Models\PropertyType;

/**
 * Class PropertyTypeRepository
 * @package Modules\PeruProperties\Repositories
 * @version Ago 22, 2019, 2:19 am UTC
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
