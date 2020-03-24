<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Partner;
use App\Models\Dashboard\PartnerProject;
use App\Models\Dashboard\Project;
use App\Repositories\BaseRepository;

/**
 * Class PartnerProjectRepository
 * @package App\Repositories\Dashboard
 * @version March 18, 2020, 20:34 UTC
*/
class PartnerProjectRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'partner_code',
        'project_code',
        'default',
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
        return PartnerProject::class;
    }

    /**
     * Return dafault partner-project
     *
     * @return array
     */
    public function getDefaultPartnerProject()
    {
        return PartnerProject::where( 'default', 'default' )->first();
    }

    /**
     * Return given partner-project
     *
     * @param  Partner|string  $partner
     * @param  Project|string  $project
     *
     * @return PartnerProject|null
     */
    public function getPartnerProject( $partner, $project )
    {
        if ( $partner instanceof Partner ) {
            $partner = $partner->code;
        }

        if ( $project instanceof Project ) {
            $project = $project->code;
        }

        return PartnerProject::where( 'partner_code', $partner )
            ->where( 'project_code' , $project )
            ->first();
    }
}
