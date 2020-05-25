<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\DominicanaProperties\Models\PublicationType;

/**
 * Class PublicationTypeRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 25, 2020, 04:58 UTC
*/
class PublicationTypeRepository extends Repository
{
    /**
     * Configure the Model
     *
     * @return string
     */
    public function model()
    {
        return PublicationType::class;
    }
}
