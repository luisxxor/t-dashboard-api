<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\ProjectAccessRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class ProjectsAccessAPIController
 * @package App\Http\Controllers\API\Admin
 */
class ProjectsAccessAPIController extends AppBaseController
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
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function update( $id, Request $request )
    {
        $request->validate( [
            'status' => [ 'required', 'string', Rule::in( array_values( config( 'constants.PROJECT_ACCESS_REQUESTS' ) ) ) ],
        ] );

        // input
        $input = $request->only( [ 'status' ] );

        // if approved...
        if ( $request->get( 'status' ) === config( 'constants.PROJECT_ACCESS_REQUESTS.APPROVED_STATUS' ) ) {
            // get projectAccessRequest
            $projectAccessRequest = $this->projectAccessRequestRepository->find( $id );

            // get user
            $user = $projectAccessRequest->user;

            $partnerProject = [
                'partner' => $projectAccessRequest->partnerProject->partner_code,
                'project' => $projectAccessRequest->partnerProject->project_code,
            ];

            // get actual accessible partner-projects
            $accessibleProjects = $user->accessible_projects;

            // add requested partner-project to the user
            $accessibleProjects[] = $partnerProject;
            $user->accessible_projects = $accessibleProjects;
            $user->save();
        }

        $projectAccessRequest = $this->projectAccessRequestRepository->update( $input, $id );

        return $this->sendResponse( $projectAccessRequest, 'Updated.' );
    }
}
