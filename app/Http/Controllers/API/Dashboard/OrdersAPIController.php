<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\FileHandler;
use App\Repositories\Dashboard\ProjectRepository;
use App\Repositories\Dashboard\OrderRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class OrdersAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class OrdersAPIController extends AppBaseController
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var  OrderRepository
     */
    private $orderRepository;

    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( OrderRepository $orderRepo,
        ProjectRepository $projectRepo )
    {
        $this->fileHandler = new FileHandler();
        $this->orderRepository = $orderRepo;
        $this->projectRepository = $projectRepo;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/orders",
     *     operationId="index",
     *     tags={"Orders"},
     *     summary="Display a listing of the user's orders",
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
     *         description="Orders retrived successfully.",
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

        $orders = auth()->user()->orders()->get()->sortByDesc( 'created_at' );

        // solo las compras concretadas y del project actual
        $orders = $orders->filter( function ( $item, $index ) use ( $projectCode ) {
            return $item->status === config( 'constants.ORDERS_RELEASED_STATUS' )
                && $item->project === $projectCode;
        } );

        return $this->sendResponse( array_values( $orders->toArray() ), 'Order retrived successfully.' );
    }

    /**
     * @param  string $orderCode
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="/api/dashboard/orders/{orderCode}/records",
     *     operationId="show",
     *     tags={"Orders"},
     *     summary="Display the specified user's order",
     *     @OA\Parameter(
     *         name="orderCode",
     *         description="code of order",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrived successfully.",
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
     *         description="Order File not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function show( $orderCode )
    {
        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();

        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', $orderCode );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        // validate if the order belongs to the user
        if ( $order->user_id != auth()->user()->getKey() ) {
            throw new AuthorizationException;
        }

        $fileInfo = collect( $order->files_info )->filter( function ( $item, $index ) {
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

        return $this->sendResponse( $output, 'Order retrived successfully.' );
    }
}