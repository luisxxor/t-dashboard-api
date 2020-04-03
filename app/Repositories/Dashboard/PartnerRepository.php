<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Partner;
use App\Models\Dashboard\PartnerProject;
use App\Models\Dashboard\Project;
use App\Repositories\BaseRepository;

/**
 * Class PartnerRepository
 * @package App\Repositories\Dashboard
 * @version March 18, 2020, 01:10 UTC
*/
class PartnerRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Partner::class;
    }
}
