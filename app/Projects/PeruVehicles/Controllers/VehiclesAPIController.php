<?php

namespace App\Projects\PeruVehicles\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\FileHandler;
use App\Projects\PeruVehicles\Repositories\VehicleRepository;
use App\Projects\PeruVehicles\Repositories\PublicationTypeRepository;
use App\Projects\PeruVehicles\Repositories\SearchRepository;
use App\Repositories\Dashboard\OrderRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Class AutosAPIController
 * @package App\Projects\PeruVehicles\Controllers
 */
class VehiclesAPIController extends AppBaseController
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var ConditionTypeRepository
     */
    private $publicationTypeRepository;

    /**
     * @var SearchRepository
     */
    private $searchRepository;

    /**
     * @var VehicleRepository
     */
    private $vehicleRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PublicationTypeRepository $publicationTypeRepo,
        SearchRepository $searchRepo,
        VehicleRepository $vehicleRepo,
        OrderRepository $orderRepo )
    {
        $this->fileHandler = new FileHandler();
        $this->publicationTypeRepository = $publicationTypeRepo;
        $this->searchRepository = $searchRepo;
        $this->vehicleRepository = $vehicleRepo;
        $this->orderRepository = $orderRepo;
    }

    /**
     * Store matched properties as searched properties from given search.
     *
     * @param Search $search The search model to store the matched properties.
     *
     * @return array
     */
    public function getPublicationTypeFilterData()
    {
        // select
        $publicationTypes = $this->publicationTypeRepository->distinct( 'name' );

        // publication types
        $publicationTypes = array_column( $publicationTypes->toArray(), 0 );

        // sort
        sort( $publicationTypes );

        return $this->sendResponse( $publicationTypes, 'Data retrieved.' );
    }

    public function getFieldFilterData($field,$publication_type)
    {
        $make = $this->vehicleRepository->distinct( $field ,$publication_type);

        if (!empty($make)) {
            // make types
            $make = array_column( $make->toArray(), 0 );

            // sort
            sort( $make );
        }

        return $this->sendResponse( $make, 'Data retrieved.' );
    }

    public function searchVehicles( Request $request )
    {
        $request->validate( [
            'publication_type'   => [ 'required', 'string' ],
            'filters'   => [ 'nullable', 'array' ],
            'perpage'   => [ 'required', 'integer', 'min:10', 'max:500'],
        ] );

        // input
        $filters    = $request->get( 'filters' );
        $perpage    = $request->get( 'perpage' ) ?? 500;

        // get user
        $user = auth()->user();

        // metadata data
        $searchData = [
            'user_id' => $user->id,
            'publication_type' => $request->publication_type,
            'metadata' => [
                'filters' => (object)$filters,
            ],
            'created_at' => new DateTime( 'now' )
        ];

        // insert into 'searches' collection
        $search = $this->searchRepository->create( $searchData );

        // construct and execute query.
        // this will store the matched properties
        // in searched_properties collection.
        $this->vehicleRepository->storeSearchedVehicle( $search );

        // paginate data (default)
        $page   = 1;
        $field  = 'publication_date';
        $sort   = -1;

        // construct and execute query
        $results = $this->vehicleRepository->getSearchedVehicles( $search->_id, compact( 'page', 'perpage', 'field', 'sort' ) );

        if ( empty( $results ) === true ) {
            return $this->sendError( 'Properties retrieved successfully.', $results, 204 );
        }

        return $this->sendResponse( $results, 'Properties retrieved successfully.' );
    }

    public function paginateVehicles( Request $request )
    {
        $request->validate( [
            'searchId'  => [ 'required', 'string' ],
            'page'      => [ 'required', 'integer', 'min:1' ],
            'perpage'   => [ 'required', 'integer', 'min:1', 'max:500'], #
            'field'     => [ 'nullable', 'string', Rule::notIn( [ 'distance', '_id' ] ) ],
            'sort'      => [ 'nullable', 'integer', 'in:1,-1' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $page       = $request->get( 'page' )       ?? 1;
        $perpage    = $request->get( 'perpage' )    ?? 10;
        $field      = $request->get( 'field' )      ?? 'publication_date';
        $sort       = $request->get( 'sort' )       ?? -1;

        // construct and execute query
        $results = $this->vehicleRepository->getSearchedVehicles( $searchId, compact( 'page', 'perpage', 'field', 'sort' ) );

        return $this->sendResponse( $results, 'Properties retrieved successfully.' );
    }

    public function order( Request $request )
    {
        $request->validate( [
            'searchId'  => [ 'required', 'string' ],
            'ids'       => [ 'required', 'array' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $ids        = $request->get( 'ids' );

        // get user
        $user = auth()->user();

        // get order if exist
        $order = $this->orderRepository->findByField( 'search_id', $searchId )->first();

        // get selected ids by user
        if ( $ids === [ '*' ] ) {
            $total = $this->vehicleRepository->countSearchedVehicles( $searchId );
        }
        else {
            $total = count( $ids );
        }

        // if order doesn't exist
        if ( empty( $order ) === true ) {
            // create order
            $order = $this->orderRepository->create( [
                'user_id'               => $user->id,
                'search_id'             => $searchId,
                'project'               => config( 'multi-api.pe-vehicles.backend-info.code' ),
                'total_rows_quantity'   => $total,
                'status'                => config( 'constants.ORDERS_OPENED_STATUS' ),
            ] );
        }
        else {
            // update order
            $order->total_rows_quantity = $total;
            $order->save();
        }

        // update the search to save selected ids by user
        $this->vehicleRepository->updateSelectedSearchedVehicles( $searchId, $ids );

        // if user has permission to release order without paying, generate file
        if ( $user->hasPermissionTo( 'release.order.without.paying' ) === true ) {

            // generate files request
            $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
            $guzzleClient->sendAsync( new GuzzleRequest(
                'GET',
                route( 'api.' . config( 'multi-api.pe-vehicles.backend-info.generate_file_url' ), [], false ),
                [ 'Content-type' => 'application/json' ],
                json_encode( [ 'orderCode' => $order->code ] )
            ) )->wait( false );

            // release order.
            $order->status = config( 'constants.ORDERS_RELEASED_STATUS' );
            $order->save();

            return $this->sendResponse( $order, 'Ordered successfully, file generated.', 202 );
        }

        // return payment init point link
        return $this->sendResponse( $order, 'Ordered successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/generate_file",
     *     operationId="generatePropertiesFile",
     *     tags={"Peru Properties"},
     *     summary="Build the order files",
     *     @OA\Parameter(
     *         name="orderCode",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Properties' file generated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="string"
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
     *         description="Order not found."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="The given data was invalid."
     *     )
     * )
     */
    public function generateVehiclesFile( Request $request )
    {
        $request->validate( [
            'orderCode'  => [ 'required', 'string' ],
        ] );

        // input
        $orderCode = $request->get( 'orderCode' );

        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();
        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', [ $orderCode ] );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        $filesInfo = [];

        // quantity of rows
        $rowsQuantity = $this->vehicleRepository->countSelectedSearchedVehicles( $order->search_id );

        // create json
        try {

            // get search
            $search = $this->searchRepository->findOrFail( $order->search_id );

            // get selected searched properties by user
            $selectedSearchedVehicles = $this->vehicleRepository->getSelectedSearchedVehicles( $order->search_id );

            $filesInfo[] = $this->fileHandler->createAndUploadFile(
                array_merge( $search->toArray(), [ 'data' => $selectedSearchedVehicles ] ),
                $rowsQuantity,
                $orderCode,
                'json',
                config( 'app.pe_export_file_bucket' )
            );
        

            // free memory
            unset( $search );
            unset( $selectedSearchedVehicles );
            gc_collect_cycles();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        // create excel
        try {

            $filesInfo[] = $this->fileHandler->createAndUploadFile(
                [
                    'header'    => $this->vehicleRepository->header,
                    'body'      => $this->vehicleRepository->getSelectedSearchedVehiclesExcelFormat( $order->search_id ),
                ],
                $rowsQuantity,
                $orderCode,
                'xlsx',
                config( 'app.pe_export_file_bucket' )
            );

            gc_collect_cycles();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        $order->files_info = $filesInfo;
        $order->save();

        return $this->sendResponse( 'OK', 'Properties\' file generated successfully.' );
    }
}
