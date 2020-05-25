<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\DominicanaProperties\Models\PropertyType;

/**
 * Class PropertyTypeRepository
 * @package Modules\DominicanaProperties\Repositories
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
