<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\FileHandler;
use App\Repositories\Dashboard\ProjectRepository;
use App\Repositories\Dashboard\PurchaseRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class PurchasesAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class PurchasesAPIController extends AppBaseController
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var  PurchaseRepository
     */
    private $purchaseRepository;

    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PurchaseRepository $purchaseRepo,
        ProjectRepository $projectRepo )
    {
        $this->fileHandler = new FileHandler();
        $this->purchaseRepository = $purchaseRepo;
        $this->projectRepository = $projectRepo;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/purchases",
     *     operationId="index",
     *     tags={"Purchases"},
     *     summary="Display a listing of the user's purchase files",
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
     *         description="Purchase files retrived successfully.",
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
     *         response=422,
     *         description="The given data was invalid."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function index( Request $request )
    {
        $request->validate( [
            'project' => 'required|string',
        ] );

        $projectCode = $request->get( 'project' );

        $project = $this->projectRepository->findByField( 'code', $projectCode );

        // validate project
        if ( empty( $project ) === true || $project->isEmpty() === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $purchases = auth()->user()->purchases()->get();

        // solo las compras concretadas y del project actual
        $purchases = $purchases->filter( function ( $item, $index ) use ( $projectCode ) {
            return $item->status === config( 'constants.PURCHASES_RELEASED_STATUS' )
                && $item->project === $projectCode;
        } );

        return $this->sendResponse( array_values( $purchases->toArray() ), 'Purchase retrived successfully.' );
    }

    /**
     * @param  string $purchaseCode
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="/api/dashboard/purchases/{purchaseCode}/records",
     *     operationId="show",
     *     tags={"Purchases"},
     *     summary="Display the specified user's purchase",
     *     @OA\Parameter(
     *         name="purchaseCode",
     *         description="code of purchase",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase file retrived successfully.",
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
     *         response=403,
     *         description="Access Denied."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Purchase File not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function show( $purchaseCode )
    {
        // get purchase
        $purchase = $this->purchaseRepository->findByField( 'code', $purchaseCode )->first();

        // validate purchase
        if ( empty( $purchase ) === true ) {
            \Log::info( 'Purchase not found.', $purchaseCode );

            return $this->sendError( 'Purchase not found.', [], 404 );
        }

        // validate if the purchase belongs to the user
        if ( $purchase->user_id != auth()->user()->getKey() ) {
            throw new AuthorizationException;
        }

        $fileInfo = collect( $purchase->files_info )->filter( function ( $item, $index ) {
            return $item[ 'type' ] === 'json';
        } )->first();

        // get file
        $filePath = $this->fileHandler->downloadFile( $fileInfo[ 'bucket' ], $fileInfo[ 'name' ] );

        // open file
        $fp = fopen( $filePath, 'r' );
        $content = fread( $fp, filesize( $filePath ) );
        $decodedContent = json_decode( $content, true );

        // output
        $output = [
            'data' => $decodedContent[ 'data' ],
            'metadata' => $decodedContent[ 'metadata' ],
        ];

        return $this->sendResponse( $output, 'Purchase file retrived successfully.' );
    }
}
