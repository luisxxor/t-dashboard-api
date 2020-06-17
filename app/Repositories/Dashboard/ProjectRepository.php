<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Project;
use App\Repositories\BaseRepository;
use Exception;

/**
 * Class ProjectRepository
 * @package App\Repositories\Dashboard
 * @version October 2, 2019, 22:20 pm UTC
*/
class ProjectRepository extends BaseRepository
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
        return Project::class;
    }

    /**
     * Return is_free value of given project.
     *
     * @param string $projectCode
     * @return bool
     */
    public function isFree( string $projectCode ): bool
    {
        try {
            return (bool)Project::find( $projectCode )->is_free;
        } catch ( Exception $e ) {
            return false;
        }
    }
}