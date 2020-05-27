<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\DominicanaProperties\Models\Search;

/**
 * Class SearchRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version May 25, 2020, 04:58 UTC
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
