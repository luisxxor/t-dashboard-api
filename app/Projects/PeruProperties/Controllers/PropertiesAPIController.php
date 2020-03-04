<?php

namespace App\Projects\PeruProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\GoogleStorageHandler;
use App\Lib\Writer\FileHandler;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use App\Projects\PeruProperties\Repositories\PropertyTypeRepository;
use App\Projects\PeruProperties\Repositories\SearchRepository;
use App\Repositories\Dashboard\OrderRepository;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class PropertiesAPIController
 * @package App\Projects\PeruProperties\Controllers
 */
class PropertiesAPIController extends AppBaseController
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var PropertyTypeRepository
     */
    private $propertyTypeRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var SearchRepository
     */
    private $searchRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PropertyTypeRepository $propertyTypeRepo,
        PropertyRepository $propertyRepo,
        SearchRepository $searchRepo,
        OrderRepository $orderRepo )
    {
        $this->fileHandler = new FileHandler();
        $this->googleStorageHandler = new GoogleStorageHandler();
        $this->propertyTypeRepository = $propertyTypeRepo;
        $this->propertyRepository = $propertyRepo;
        $this->searchRepository = $searchRepo;
        $this->orderRepository = $orderRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/filters/property_type",
     *     operationId="getPropertyTypeFilterData",
     *     tags={"Peru Properties"},
     *     summary="Return the necessary data for property type filter",
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
     *                     type="array",
     *                     @OA\Items()
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
    public function getPropertyTypeFilterData()
    {
        // select
        $propertyTypes = $this->propertyTypeRepository->distinct( 'owner_name' );

        // property types
        $propertyTypes = array_column( $propertyTypes->toArray(), 0 );

        // sort
        sort( $propertyTypes );

        return $this->sendResponse( $propertyTypes, 'Data retrieved.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/ghost_search",
     *     operationId="ghostSearch",
     *     tags={"Peru Properties"},
     *     summary="Make the ghost search",
     *     @OA\Parameter(
     *         name="lat",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="maxDistance",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="int"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data retrieved successfully.",
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
     *                     type="array",
     *                     @OA\Items()
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
     *         response=422,
     *         description="The given data was invalid."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function ghostSearch( Request $request )
    {
        $request->validate( [
            'lat'           => [ 'required', 'numeric' ],
            'lng'           => [ 'required', 'numeric' ],
            'maxDistance'   => [ 'required', 'integer', 'min:1', 'max:5000' ],
        ] );

        // input
        $lat            = $request->get( 'lat' );
        $lng            = $request->get( 'lng' );
        $maxDistance    = $request->get( 'maxDistance' );

        // construct and execute query.
        // search properties
        $this->propertyRepository->searchPropertiesOnlyByGeonear( $lat, $lng, $maxDistance );

        return $this->sendResponse( [], 'Success.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/peru_properties/search",
     *     operationId="searchProperties",
     *     tags={"Peru Properties"},
     *     summary="Return the properties that math with given filters",
     *     @OA\Parameter(
     *         name="vertices",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lat",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data retrieved successfully.",
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
     *                     type="array",
     *                     @OA\Items()
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The request has been successfully completed but your answer has no content"
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
    public function searchProperties( Request $request )
    {
        $request->validate( [
            'vertices'  => [ 'required', 'array', 'filled' ],
            'filters'   => [ 'nullable', 'array' ],
            'lat'       => [ 'required', 'numeric' ],
            'lng'       => [ 'required', 'numeric' ],
            'address'   => [ 'nullable', 'string' ],
            'perpage'   => [ 'required', 'integer', 'min:10', 'max:500' ],
        ] );

        // input
        $vertices   = $request->get( 'vertices' );
        $filters    = $request->get( 'filters' );
        $lat        = $request->get( 'lat' );
        $lng        = $request->get( 'lng' );
        $address    = $request->get( 'address' );
        $perpage    = $request->get( 'perpage' );

        // paginate data (default)
        $field  = 'publication_date';
        $sort   = -1;

        // get user
        $user = auth()->user();

        // metadata data
        $searchData = [
            'user_id' => $user->id,
            'metadata' => [
                'vertices' => $vertices,
                'filters' => (object)$filters,
                'initPoint' => [
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                    'address' => $address,
                ],
            ],
            'created_at' => new DateTime( 'now' )
        ];

        // insert into 'searches' collection
        $search = $this->searchRepository->create( $searchData );

        // construct and execute query.
        // this will return the matched properties.
        $data = $this->propertyRepository->searchPropertiesReturnOutputFields( $search, compact( 'perpage', 'field', 'sort' ) );

        if ( empty( $data ) === true ) {
            return $this->sendError( 'No properties matched.', $data, 204 );
        }

        return $this->sendResponse( $data, 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/paginate",
     *     operationId="paginateProperties",
     *     tags={"Peru Properties"},
     *     summary="Return the properties that math with given search id",
     *     @OA\Parameter(
     *         name="searchId",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lastItem",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="field",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data retrieved successfully.",
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
     *                     type="array",
     *                     @OA\Items()
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The request has been successfully completed but your answer has no content"
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
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function paginateProperties( Request $request )
    {
        $request->validate( [
            'searchId'      => [ 'required', 'string' ],
            'lastItem'      => [ 'required', 'array', 'filled' ],
            'perpage'       => [ 'required', 'integer', 'min:1', 'max:500' ],
            'field'         => [ 'nullable', 'string', Rule::notIn( [ 'distance', '_id' ] ) ],
            'sort'          => [ 'nullable', 'integer', 'in:1,-1' ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $lastItem   = $request->get( 'lastItem' );
        $perpage    = $request->get( 'perpage' );
        $field      = $request->get( 'field' )      ?? 'publication_date';
        $sort       = $request->get( 'sort' )       ?? -1;

        try {
            // get search model
            $search = $this->searchRepository->findOrFail( $searchId );

            // construct and execute query
            $data = $this->propertyRepository->searchPropertiesReturnOutputFields( $search, compact( 'perpage', 'field', 'sort', 'lastItem' ) );
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage(), [], 404 );
        }

        if ( empty( $data[ 'data' ] ) === true ) {
            return $this->sendError( 'No properties matched.', $data, 204 );
        }

        return $this->sendResponse( $data, 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/peru_properties/order",
     *     operationId="order",
     *     tags={"Peru Properties"},
     *     summary="Order items",
     *     description="Create order in case it does not, and update the 'ids' value of search",
     *     @OA\Parameter(
     *         name="searchId",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="ids",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ordered successfully.",
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
     *         response=202,
     *         description="Ordered successfully, file generated."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description=" Bad Request."
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
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
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

        try {
            // get search model
            $search = $this->searchRepository->findOrFail( $searchId );
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage(), [], 404 );
        }

        // get selected ids by user
        if ( $ids === [ '*' ] ) {
            $total = $this->propertyRepository->countSearchedProperties( $search ); # esto hace una consulta
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
                'project'               => config( 'multi-api.pe-properties.backend-info.code' ),
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
        $this->propertyRepository->updateSelectedPropertiesInSearch( $search, $ids );

        // if user has permission to release order without paying, generate file
        if ( $user->hasPermissionTo( 'release.order.without.paying' ) === true ) {
            // generate files request
            $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
            $guzzleClient->sendAsync( new GuzzleRequest(
                'GET',
                route( 'api.' . config( 'multi-api.pe-properties.backend-info.generate_file_url' ), [], false ),
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
    public function generatePropertiesFile( Request $request )
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

        try {
            // get search
            $search = $this->searchRepository->findOrFail( $order->search_id );

            // create json metadata file
            $jsonMetadataFile = FileHandler::createWriter( 'json' )
                ->openToFile( $orderCode . '.metadata.json' )
                ->addRow( json_encode( $search->toArray() ) );
            $path = $jsonMetadataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( config( 'app.pe_export_file_bucket' ), $path, $order->total_rows_quantity );

            // free memory
            gc_collect_cycles();

            // create json data file
            $jsonDataFile = FileHandler::createWriter( 'json' )
                ->openToFile( $orderCode . '.json' );

            // create xlsx data file
            $xlsxDataFile = FileHandler::createWriter( 'xlsx' )
                ->openToFile( $orderCode . '.xlsx' );
            $xlsxDataFile->addRow( $this->propertyRepository->header, true );

            $perpage = 25;
            $lastItem = [];
            do {
                $selectedSearchedProperties = $this->propertyRepository->getSelectedPropertiesFromProperties( $search, compact( 'perpage', 'lastItem' ) );

                foreach ( $selectedSearchedProperties as $item ) {
                    // add json data row
                    $jsonDataFile->addRow( $this->createJSONRow( $item ) );

                    // add xlsx data row
                    $xlsxDataFile->addRow( $this->createXLSXRow( $item ) );
                }

                $lastItem = [
                    '_id' => $item[ '_id' ],
                    'publication_date' => $item[ 'publication_date' ],
                ];
            } while ( empty( $selectedSearchedProperties ) === false );

            // close json data file
            $path = $jsonDataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( config( 'app.pe_export_file_bucket' ), $path, $order->total_rows_quantity );

            // close xslx data file
            $path = $xlsxDataFile->close();
            $filesInfo[] = $this->googleStorageHandler->uploadFile( config( 'app.pe_export_file_bucket' ), $path, $order->total_rows_quantity );

            // free memory
            unset( $search );
            unset( $selectedSearchedProperties );
            gc_collect_cycles();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        $order->files_info = $filesInfo;
        $order->save();

        return $this->sendResponse( 'OK', 'Properties\' file generated successfully.' );
    }

    /**
     * Creates a custom format row for json data file.
     *
     * @param array $item The item that needs to be formatted to the row.
     *
     * @return string
     */
    protected function createJSONRow( array $item )
    {
        return json_encode( $item, JSON_UNESCAPED_SLASHES ) . PHP_EOL;
    }

    /**
     * Creates a custom format row for xlsx data file.
     *
     * @param array $item The item that needs to be formatted to the row.
     *
     * @return array
     */
    protected function createXLSXRow( array $item )
    {
        // discrimination of fields to xlsx file
        $xlsxFields = collect( $item )->only( array_keys( $this->propertyRepository->header ) )->toArray();

        // merge to avoid non-existent values
        $dictionary = array_fill_keys( array_keys( $this->propertyRepository->header ), null );
        $xlsxRow = array_merge( $dictionary, $xlsxFields );

        // formatting that needs to be done
        $formatting = [
            'publication_date' => function ( $value ) {
                return Carbon::createFromFormat( 'Y-m-d H:i:s', $value )->format( 'd-m-Y' );
            },
            'distance' => function ( $value ) {
                return (int)round( $value, 0 );
            }
        ];

        // format
        foreach ( $formatting as $key => $callable ) {
            if ( empty( $xlsxRow[ $key ] ) === false ) {
                $xlsxRow[ $key ] = $callable( $xlsxRow[ $key ] );
            }
        }

        return $xlsxRow;
    }
}
