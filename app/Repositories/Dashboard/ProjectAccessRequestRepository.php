<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\ProjectAccessRequest;
use App\Repositories\BaseRepository;

/**
 * Class ProjectAccessRequestRepository
 * @package App\Repositories\Dashboard
 * @version March 18, 2020, 20:28 UTC
*/
class ProjectAccessRequestRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'partner_project_id',
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
        return ProjectAccessRequest::class;
    }
}
