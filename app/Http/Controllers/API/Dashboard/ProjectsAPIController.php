<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\ProjectRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

/**
 * Class ProjectsAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class ProjectsAPIController extends AppBaseController
{
    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ProjectRepository $projectRepo )
    {
        $this->projectRepository = $projectRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/multi-api/index",
     *     operationId="index",
     *     tags={"Scrapings APIs"},
     *     summary="Display the list of projects (scraping projects)",
     *     @OA\Response(
     *         response=200,
     *         description="Projects data retrived.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="filters",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(
     *                             property="field-1",
     *                             type="string"
     *                         )
     *                     )
     *                 ),
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
        $projects = $this->projectRepository->all();

        return $this->sendResponse( $projects, 'Filters data retrived.' );
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/multi-api/get_front_info",
     *     operationId="getFrontInfo",
     *     tags={"Scrapings APIs"},
     *     summary="Get the front info for given project.",
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
     *         description="Projects retrived successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items()
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
     *         description="Project not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function getFrontInfo( Request $request )
    {
        $request->validate( [
            'project' => 'required|string',
        ] );

        // formato del archivo
        $projectCode = $request->get( 'project' );

        // validate project
        $project = $this->projectRepository->findByField( 'code', $projectCode );

        if ( $project->isEmpty() === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $frontInfo = config( 'multi-api.' . $projectCode . '.front-info' );

        return $this->sendResponse( $frontInfo, 'Front info retrived successfully.' );
    }
}
