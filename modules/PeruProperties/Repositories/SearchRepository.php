<?php

namespace Modules\PeruProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\PeruProperties\Models\Search;

/**
 * Class SearchRepository
 * @package Modules\PeruProperties\Repositories
 * @version Jun 28, 2019, 06:34 UTC
*/
class SearchRepository extends Repository
{
    /**
     * Configure the Model
     *
     * @return string
     */
    public function model()
    {
        return Search::class;
    }
}
