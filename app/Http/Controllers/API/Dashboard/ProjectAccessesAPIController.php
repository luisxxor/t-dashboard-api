<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\PartnerProjectRepository;
use App\Repositories\Dashboard\PartnerRepository;
use Illuminate\Http\Request;

/**
 * Class ProjectAccessesAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class ProjectAccessesAPIController extends AppBaseController
{
    /**
     * @var  PartnerRepository
     */
    private $partnerRepository;

    /**
     * @var  PartnerProjectRepository
     */
    private $partnerProjectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PartnerRepository $partnerRepo,
        PartnerProjectRepository $partnerProjectRepo )
    {
        $this->partnerRepository = $partnerRepo;
        $this->partnerProjectRepository = $partnerProjectRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/projects_access",
     *     operationId="index",
     *     tags={"Project Access"},
     *     summary="Display the list of accessible and requested partner-projects.",
     *     @OA\Response(
     *         response=200,
     *         description="Data retrieved.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function index()
    {
        $user = auth()->user();

        $accessibleProjectList = $this->getAccessiblePartnerProjects( $user );

        $requestedProjects = $user->getProjectAccessRequestList();

        $response = [
            'accessibleProjects' => $accessibleProjectList,
            'requestedProjects' => $requestedProjects,
        ];

        return $this->sendResponse( $response, 'Data retrieved.' );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/dashboard/projects_access/request",
     *     operationId="request",
     *     tags={"Project Access"},
     *     summary="Create an access request to the given partner-project.",
     *     @OA\Response(
     *         response=200,
     *         description="Request done.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Accepted. User already has the given partner-project or has a created request for this partner-project"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function request( Request $request )
    {
        $request->validate( [
            'partner' => [ 'required', 'string', 'filled' ],
            'project' => [ 'required', 'string', 'filled' ],
        ] );

        // input
        $partnerCode    = $request->get( 'partner' );
        $projectCode    = $request->get( 'project' );

        $user = auth()->user();

        // get partner-project
        $partnerProject = $this->partnerProjectRepository->getPartnerProject( $partnerCode, $projectCode );

        // validate that the partner-project is valid
        if ( empty( $partnerProject ) === true ) {
            return $this->sendError( 'Partner or project not valid.' );
        }

        // validates if the user has already access to this partner-project
        if ( $user->hasPartnerProjectAccess( $partnerProject ) === true ) {
            return $this->sendResponse( [], 'User already has the given partner-project.', 202 );
        }

        // validates if the user has a created request for this partner-project
        if ( $user->hasPartnerProjectPendingRequest( $partnerProject ) === true ) {
            return $this->sendResponse( [], 'User has a created request for this partner-project.', 202 );
        }

        $projectAccessRequest = $partnerProject->requests()->create(
            [
                'user_id' => $user->id,
                'status' => config( 'constants.PROJECT_ACCESS_REQUESTS.STATUS.PENDING' )
            ]
        );

        return $this->sendResponse( $projectAccessRequest, 'Request done.' );
    }

    /**
     * Gets accessible partner-projects of given user.
     *
     * @param \App\Models\Dashboard\User $user
     *
     * @return array
     */
    protected function getAccessiblePartnerProjects( $user )
    {
        $accessibleProjects = $user->accessible_projects;

        $accessibleProjectList = [];
        foreach ( $accessibleProjects as $accessibleProject ) {
            $partnerProject = $this->partnerProjectRepository->getPartnerProject( $accessibleProject[ 'partner' ], $accessibleProject[ 'project' ] );

            if ( empty( $partnerProject ) === true ) {
                continue;
            }

            $accessibleProjectList[] = [
                'partner' => $partnerProject->partner,
                'project' => $partnerProject->project,
            ];
        }

        return $accessibleProjectList;
    }
}
