<?php

namespace App\Http\Controllers\API\Subscriptions;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\PartnerProjectRepository;
use App\Repositories\Dashboard\PartnerRepository;
use Illuminate\Http\Request;

/**
 * Class SubscriptionsAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class SubscriptionsAPIController extends AppBaseController
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

        // input
        $projectCode    = $request->get( 'project' );

        $user = auth()->user();

        # TODO: solo las del proyecto indicado en request

        $subscriptions = $user->subscriptions()->with( [ 'planProject', 'realPlan' ] )->get();

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

        // input
        $projectCode    = $request->get( 'project' );

        $user = auth()->user();

        # TODO
        $planProjects = [];

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

        // input
        $planProject = $request->get( 'planProjectId' );

        $user = auth()->user();

        # TODO
        $availablePlans = [];

        return $this->sendResponse( $availablePlans, 'Data retrieved.' );
    }
}
