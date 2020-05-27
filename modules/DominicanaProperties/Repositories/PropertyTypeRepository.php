<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\DominicanaProperties\Models\PropertyType;

/**
 * Class PropertyTypeRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 27, 2020, 17:49 UTC
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
