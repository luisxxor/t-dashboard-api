<?php

namespace App\Http\Controllers\API\Subscriptions;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\Subscriptions\PlanProject as PlanProjectResource;
use App\Http\Resources\Subscriptions\Subscription as SubscriptionResource;
use App\Models\Subscriptions\PlanProject;
use App\Repositories\Dashboard\PartnerProjectRepository;
use App\Repositories\Dashboard\ProjectRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

/**
 * Class SubscriptionsAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class SubscriptionsAPIController extends AppBaseController
{
    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * @var  PartnerProjectRepository
     */
    private $partnerProjectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ProjectRepository $projectRepo,
        PartnerProjectRepository $partnerProjectRepo )
    {
        $this->projectRepository = $projectRepo;
        $this->partnerProjectRepository = $partnerProjectRepo;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/subscriptions",
     *     operationId="index",
     *     tags={"Subscriptions"},
     *     summary="Display the list of user's subscriptions for given project.",
     *     @OA\Parameter(
     *         name="project",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function index( Request $request )
    {
        $request->validate( [
            'project' => [ 'required', 'string', 'filled' ],
        ] );

        $projectCode = $request->get( 'project' );

        $project = $this->projectRepository->find( $projectCode );

        if ( empty( $project ) === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $user = auth()->user();

        if ( $user->hasProjectAccess( $projectCode ) === false ) {
            throw new AuthorizationException;
        }

        $planProjectIds = PlanProject::byProject( $projectCode )->pluck( 'id' );

        $subscriptions = SubscriptionResource::collection(
            $user->subscriptions()->with( [ 'realPlan', 'planProject', 'usage' ] )->whereIn( 'plan_project_id', $planProjectIds )->get()
        );

        return $this->sendResponse( $subscriptions, 'Data retrieved.' );
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/subscriptions/available_plans",
     *     operationId="availablePlans",
     *     tags={"Subscriptions"},
     *     summary="Display the list of available plans for given project.",
     *     @OA\Parameter(
     *         name="project",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *     @OA\Response(
     *         response=404,
     *         description="Data not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function availablePlans( Request $request )
    {
        $request->validate( [
            'project' => [ 'required', 'string', 'filled' ],
        ] );

        $projectCode = $request->get( 'project' );

        $project = $this->projectRepository->find( $projectCode );

        if ( empty( $project ) === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $user = auth()->user();

        if ( $user->hasProjectAccess( $projectCode ) === false ) {
            throw new AuthorizationException;
        }

        $subscribedPlanProjectIds = $user->subscribedPlanProjectIds()->values();

        $planProjects = PlanProjectResource::collection(
            PlanProject::byProject( $projectCode )->with( [ 'plan' ] )->whereNotIn( 'id', $subscribedPlanProjectIds )->get()
        );

        return $this->sendResponse( $planProjects, 'Data retrieved.' );
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/subscriptions/subscribe",
     *     operationId="subscribe",
     *     tags={"Subscriptions"},
     *     summary="Create a new user's subscription for given project and plan.",
     *     description="If it is a free plan, return success message. If no, return payment init point.",
     *     @OA\Parameter(
     *         name="planProjectId",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
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
    public function subscribe( Request $request )
    {
        $request->validate( [
            'planProjectId' => [ 'required', 'integer', 'filled' ],
        ] );

        $planProjectId = $request->get( 'planProjectId' );

        $planProject = PlanProject::find( $planProjectId );

        if ( empty( $planProject ) === true ) {
            return $this->sendError( 'Plan Project not found.', [], 404 );
        }

        $user = auth()->user();

        if ( $user->hasProjectAccess( $planProject->project_code ) === false ) {
            throw new AuthorizationException;
        }

        // check if user already has the given partner project
        $subscribedPlanProjectIds = $user->subscribedPlanProjectIds()->values();
        if ( $subscribedPlanProjectIds->search( $planProjectId ) !== false ) {
            return $this->sendError( 'El usuario ya tiene este plan project.', [], 202 );
        }


        # TODO
        $availablePlans = [];

        return $this->sendResponse( $availablePlans, 'Data retrieved.' );
    }
}
