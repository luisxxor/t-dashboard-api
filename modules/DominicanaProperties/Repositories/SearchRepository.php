<?php

namespace Modules\DominicanaProperties\Repositories;

use Modules\Common\Repositories\Repository;
use Modules\DominicanaProperties\Models\Search;

/**
 * Class SearchRepository
 * @package Modules\DominicanaProperties\Repositories
 * @version Jun 28, 2019, 2:34 pm UTC
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
