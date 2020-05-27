<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\ProjectAccessRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class ProjectAccessesAPIController
 * @package App\Http\Controllers\API\Admin
 */
class ProjectAccessesAPIController extends AppBaseController
{
    /**
     * @var  ProjectAccessRequestRepository
     */
    private $projectAccessRequestRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ProjectAccessRequestRepository $projectAccessRequestRepo )
    {
        $this->projectAccessRequestRepository = $projectAccessRequestRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/admin/projects_access",
     *     operationId="index",
     *     tags={"Admin"},
     *     summary="Display the list of partner-projects access requests.",
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
        $projectAccessRequests = $this->projectAccessRequestRepository->getList();

        return $this->sendResponse( $projectAccessRequests, 'Data retrieved.' );
    }

    /**
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/admin/projects_access/{id}",
     *     operationId="update-",
     *     tags={"Admin"},
     *     summary="Update the partner-project access request with given data.",
     *     @OA\Parameter(
     *         name="id",
     *         description="id of project_access_requests",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         description="approved|denied",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated.",
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
     *         description="Cannot change status (already approved|denied)."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function update( $id, Request $request )
    {
        $request->validate( [
            'status' => [ 'required', 'string', Rule::in( array_values( config( 'constants.PROJECT_ACCESS_REQUESTS.STATUS' ) ) ) ],
        ] );

        // input
        $input = $request->only( [ 'status' ] );

        // get projectAccessRequest
        $projectAccessRequest = $this->projectAccessRequestRepository->find( $id );

        // validate order
        if ( empty( $projectAccessRequest ) === true ) {
            return $this->sendError( 'Project access request not found.', [], 404 );
        }

        // validates if the access requests already has been approved|denied
        if ( $projectAccessRequest->status === config( 'constants.PROJECT_ACCESS_REQUESTS.STATUS.APPROVED' ) ||
            $projectAccessRequest->status === config( 'constants.PROJECT_ACCESS_REQUESTS.STATUS.DENIED' ) ) {
            return $this->sendError( 'Cannot change status (already approved|denied).', [], 202 );
        }

        // if approved...
        if ( $request->get( 'status' ) === config( 'constants.PROJECT_ACCESS_REQUESTS.STATUS.APPROVED' ) ) {
            // get user
            $user = $projectAccessRequest->user;

            // add partner-project to the user
            $user->addAccessibleProject( $projectAccessRequest->partnerProject );
        }

        $projectAccessRequest = $this->projectAccessRequestRepository->update( $input, $id );

        return $this->sendResponse( $projectAccessRequest, 'Updated.' );
    }
}
