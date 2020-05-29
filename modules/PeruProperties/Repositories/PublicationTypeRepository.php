<?php

namespace Modules\PeruProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\PeruProperties\Models\PublicationType;

/**
 * Class PublicationTypeRepository
 * @package Modules\PeruProperties\Repositories
 * @version May 25, 2020, 22:02 UTC
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
