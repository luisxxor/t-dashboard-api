<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\GoogleStorageHandler;
use App\Lib\Reader\Common\FileReaderFactory;
use App\Repositories\Dashboard\OrderRepository;
use App\Repositories\Dashboard\ProjectRepository;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Response;

/**
 * Class OrdersAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class OrdersAPIController extends AppBaseController
{
    /**
     * @var GoogleStorageHandler
     */
    private $googleStorageHandler;

    /**
     * @var  OrderRepository
     */
    private $orderRepository;

    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * @var  UserRepository
     */
    private $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( OrderRepository $orderRepo,
        ProjectRepository $projectRepo,
        UserRepository $userRepo )
    {
        $this->googleStorageHandler = new GoogleStorageHandler();
        $this->orderRepository = $orderRepo;
        $this->projectRepository = $projectRepo;
        $this->userRepository = $userRepo;
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
     *     @OA\Parameter(
     *         name="userId",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="int"
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
            'project' => [ 'required', 'string' ],
            'userId' => [ 'nullable', 'integer' ],
        ] );

        $projectCode    = $request->get( 'project' );
        $userId         = $request->get( 'userId' );

        $project = $this->projectRepository->findByField( 'code', $projectCode );

        // validate project
        if ( empty( $project ) === true || $project->isEmpty() === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $user = auth()->user();

        // if user has permission to see foreign orders list
        if ( $userId !== null && $user->hasPermissionTo( 'see.foreign.orders.list' ) === true ) {
            $user = $this->userRepository->find( $userId );

            if ( empty( $user ) === true ) {
                \Log::info( 'User not found.', [ $id ] );

                return $this->sendError( 'User not found.', [], 404 );
            }
        }

        // only consistent orders and from given project
        $orders = $user->orders()->where( 'project', $projectCode )
            ->whereNotNull( 'status' )
            ->orderByDesc( 'created_at' )
            ->get();

        return $this->sendResponse( $orders, 'Order retrived successfully.' );
    }

    /**
     * @param  string $orderCode
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="/api/dashboard/orders/{orderCode}/get_file",
     *     operationId="getFile",
     *     tags={"Orders"},
     *     summary="Return the given file of the order",
     *     @OA\Parameter(
     *         name="orderCode",
     *         description="code of order",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="file",
     *         description="data|metadata",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         description="required if file='data'",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="required if file='data'",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data successfully.",
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
     *         description="Access Denied. User is not the owner of this order."
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
    public function getFile( $orderCode, Request $request )
    {
        $request->validate( [
            'file'      => [ 'required', 'string', 'in:data,metadata' ],
            'perpage'   => [
                Rule::requiredIf( function () use ( $request ) {
                    return $request->get( 'file' ) === 'data';
                } ), 'integer', 'min:1', 'max:100'
            ],
            'page'      => [
                Rule::requiredIf( function () use ( $request ) {
                    return $request->get( 'file' ) === 'data';
                } ), 'integer', 'min:1'
            ],
        ] );

        // input
        $file       = $request->get( 'file' );
        $perPage    = $request->get( 'perpage' );
        $page       = $request->get( 'page' );

        $fileType = $file === 'data' ? 'ndjson' : 'json';

        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();

        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', [ $orderCode ] );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        $user = auth()->user();

        // validate if the order belongs to the user, or user has permission to see foreign order
        if ( $order->user_id != auth()->user()->getKey() && $user->hasPermissionTo( 'see.foreign.order' ) === false ) {
            throw new AuthorizationException;
        }

        $fileInfo = collect( $order->files_info )->filter( function ( $item, $index ) use ( $fileType ) {
            return $item[ 'type' ] === $fileType;
        } )->first();

        # TODO: validar si $fileInfo esta vacio

        try {
            // get file
            $filePath = $this->googleStorageHandler->downloadFile( $fileInfo[ 'bucket' ], $fileInfo[ 'name' ] );

            // read file
            $fileReader = FileReaderFactory::createReaderFromFile( $filePath );

            switch ( $file ) {
                case 'metadata':
                    $stringContent = $fileReader->getContent();

                    $content = json_decode( $stringContent, true );
                    break;

                case 'data':
                    $paginationOptions = [
                        'limit' => $perPage,
                        'offset' => ( $page - 1 ) * $perPage
                    ];

                    $content = $fileReader->getLineIterator( function ( $line ) {
                            return json_decode( $line, true );
                        }, $paginationOptions
                    );
                    break;

                default:
                    throw new \Exception( 'File not found.' );
                    break;
            }

            $fileReader->close();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        return $this->sendResponse( $content, 'File retrieved successfully.' );
    }

    /**
     * @param   string $orderCode
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     * @throws  \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="api/dashboard/orders/{orderCode}/download_file",
     *     operationId="downloadFile",
     *     tags={"Orders"},
     *     summary="Download the export file",
     *     description="Returns the download link of file",
     *     @OA\Parameter(
     *         name="orderCode",
     *         description="code of order",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         description="xlsx",
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
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="string"
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
     *     @OA\Response(
     *         response=403,
     *         description="Access Denied. User is not the owner of this order."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found."
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
    public function downloadFile( $orderCode, Request $request )
    {
        $request->validate( [
            'format' => [ 'required', 'string', 'in:xlsx' ],
        ] );

        // input
        $format = $request->get( 'format' );

        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();

        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', [ $orderCode ] );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        $user = auth()->user();

        // validate if the order belongs to the user, or user has permission to download foreign order
        if ( $order->user_id != auth()->user()->getKey() && $user->hasPermissionTo( 'download.foreign.order' ) === false ) {
            throw new AuthorizationException;
        }

        $fileInfo = collect( $order->files_info )->filter( function ( $item, $index ) use ( $format ) {
            return $item[ 'type' ] === $format;
        } )->first();

        # TODO: validar si $fileInfo esta vacio

        try {
            // get file
            $filePath = $this->googleStorageHandler->downloadFile( $fileInfo[ 'bucket' ], $fileInfo[ 'name' ], false );

            // path to download the file
            $routeFilePath = route( 'downloadFiles', [ 'fileName' => basename( $filePath ) ] );
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        return $this->sendResponse( $routeFilePath, 'Download link retrieved.' );
    }
}
