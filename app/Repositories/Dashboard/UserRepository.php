<?php

namespace App\Repositories\Dashboard;

use App\Http\Resources\User as UserResource;
use App\Models\Dashboard\PartnerProject;
use App\Models\Dashboard\User;
use App\Models\Subscriptions\PlanFeature;
use App\Models\Subscriptions\PlanProject;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserRepository
 * @package App\Repositories\Dashboard
*/
class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name', 'lastname', 'email',
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
        return User::class;
    }

    /**
     * Create model record
     *
     * @param array $input
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create( $input )
    {
        $user = parent::create( $input );

        // assign default roles
        $user->assignRoles( 'regular-user' );

        // create default subscription (pay-per-download feature)
        if ( $defaultPlanProject = $this->defaultPlanProject( $user ) ) {
            $user->newSubscription( 'main', $defaultPlanProject );
        }

        return $user;
    }

    /**
     * Get default PlanProject for the project with which the user
     * has been registered.
     *
     * @param User $user
     * @return PlanProject|null
     */
    protected function defaultPlanProject( User $user ): ?PlanProject
    {
        try {
            $project = $user->accessible_projects[ 0 ][ 'project' ];
            $defaultPlanFeature = PlanFeature::bySlug( config( 'rinvex.subscriptions.features.pay-per-download' ) )->first();
            $defaultPlanProject = $defaultPlanFeature->plan->planProjects->where( 'project_code', $project )->first();
        } catch ( \Exception $e ) {
            return null;
        }

        return $defaultPlanProject;
    }

    public function login( User $user, array $dataToken )
    {
        // scopes to which the user has access
        $scopes = $user->getScopes();

        $accessToken = $user->createToken( 'authToken', $scopes )->accessToken;

        // get attempted partner-project info
        $partnerProject = PartnerProject::byPartner( $dataToken[ 'partner' ] )->byProject( $dataToken[ 'project' ] )
            ->with( [ 'partner', 'project' ] )->first();
        $attemptedPartnerProject = [
            'partner' => $partnerProject->partner,
            'project' => $partnerProject->project,
        ];

        return [
            'user' => new UserResource( $user ),
            'accessToken' => $accessToken,
            'attemptedPartnerProject' => $attemptedPartnerProject,
        ];
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param       $id
     * @return mixed
     */
    public function update( array $attributes, $id )
    {
        // not update password if is empty
        if ( array_key_exists( 'password', $attributes ) === true && empty( $attributes[ 'password' ] ) === true ) {
            unset( $attributes[ 'password' ] );
        }

        return parent::update( $attributes, $id );
    }
}
