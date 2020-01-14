<?php

namespace App\Projects\PeruProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\FileHandler;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use App\Projects\PeruProperties\Repositories\PropertyTypeRepository;
use App\Projects\PeruProperties\Repositories\SearchRepository;
use App\Repositories\Dashboard\PurchaseRepository;
use DateTime;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

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
     * @var PurchaseRepository
     */
    private $purchaseRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PropertyTypeRepository $propertyTypeRepo,
        PropertyRepository $propertyRepo,
        SearchRepository $searchRepo,
        PurchaseRepository $purchaseRepo )
    {
        $this->fileHandler = new FileHandler();
        $this->propertyTypeRepository = $propertyTypeRepo;
        $this->propertyRepository = $propertyRepo;
        $this->searchRepository = $searchRepo;
        $this->purchaseRepository = $purchaseRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/index",
     *     operationId="index",
     *     tags={"Peru Properties"},
     *     summary="Display the necessary data for filters",
     *     @OA\Response(
     *         response=200,
     *         description="Filters data retrived.",
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
        // select
        $propertyTypes = $this->propertyTypeRepository->distinct( 'owner_name' );

        // property types
        $propertyTypes = array_column( $propertyTypes->toArray(), 0 );

        // sort
        sort( $propertyTypes );

        return $this->sendResponse( $propertyTypes, 'Filters data retrived.' );
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
     *         description="Ghost search done.",
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
     *     path="/api/peru_properties/properties_ajax",
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
     *         description="Properties retrieved successfully.",
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
            'perpage'   => [ 'required', 'integer', 'min:10', 'max:500'],
        ] );

        // input
        $vertices   = $request->get( 'vertices' );
        $filters    = $request->get( 'filters' );
        $lat        = $request->get( 'lat' );
        $lng        = $request->get( 'lng' );
        $address    = $request->get( 'address' );
        $perpage    = $request->get( 'perpage' ) ?? 500;

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
        // this will store the matched properties
        // in searched_properties collection.
        $this->propertyRepository->storeSearchedProperties( $search );

        // paginate data (default)
        $page   = 1;
        $field  = 'publication_date';
        $sort   = -1;

        // construct and execute query
        $results = $this->propertyRepository->getSearchedProperties( $search->_id, compact( 'page', 'perpage', 'field', 'sort' ) );

        if ( empty( $results ) === true ) {
            return $this->sendError( 'Properties retrieved successfully.', $results, 204 );
        }

        return $this->sendResponse( $results, 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/peru_properties/properties_paginate",
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
     *         name="page",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
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
     *         description="Properties' page retrieved successfully.",
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
    public function paginateProperties( Request $request )
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
        $results = $this->propertyRepository->getSearchedProperties( $searchId, compact( 'page', 'perpage', 'field', 'sort' ) );

        return $this->sendResponse( $results, 'Properties retrieved successfully.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/peru_properties/process_purchase",
     *     operationId="processPurchase",
     *     tags={"Peru Properties"},
     *     summary="Process purchase; create payment init point.",
     *     description="Procces purchase and returns the payment init point link (if admin, generate files)",
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
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="selectAll",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase processed successfully, payment init point link sended.",
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
     *         description="Purchase processed successfully, file generated (admin user)."
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
     *         response=422,
     *         description="The given data was invalid."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function processPurchase( Request $request )
    {
        $request->validate( [
            'searchId' => [ 'required', 'string' ],
            'ids' => [ 'nullable', 'array', Rule::requiredIf( function () use ( $request ) {
                return $request->has( 'selectAll' ) === false;
            } ) ],
            'selectAll' => [ 'nullable', 'boolean', Rule::requiredIf( function () use ( $request ) {
                return $request->has( 'ids' ) === false || empty( $request->get( 'ids' ) ) === true;
            } ) ],
        ] );

        // input
        $searchId   = $request->get( 'searchId' );
        $ids        = $request->get( 'ids' ) ?? [];
        $selectAll  = $request->get( 'selectAll' );

        // get user
        $user = auth()->user();

        // get selected ids by user
        if ( $selectAll === true ) {
            $ids = [ '*' ];

            $total = $this->propertyRepository->countSearchedProperties( $searchId );
        }
        else {
            $total = count( $ids );
        }

        // update the search to save selected ids by user
        $this->propertyRepository->updateSelectedSearchedProperties( $searchId, $ids );

        // process purchase
        $purchaseAttributes = [
            'user_id'               => $user->id,
            'search_id'             => $searchId,
            'project'               => config( 'multi-api.pe-properties.backend-info.code' ),
            'total_rows_quantity'   => $total,
            'payment_type'          => config( 'constants.PAYMENTS_MERCADOPAGO' ),
            'currency'              => 'PEN',
            'status'                => config( 'constants.PURCHASES_OPENED_STATUS' ),
        ];

        try {
            $purchase = $this->purchaseRepository->process( $purchaseAttributes );
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage(), [], 400 );
        }

        // if admin, generate file. else, return payment init point link
        if ( $user->hasRole( 'admin' ) === true ) {

            // generate files request
            $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
            $guzzleClient->sendAsync( new GuzzleRequest(
                'GET',
                route( 'api.' . config( 'multi-api.pe-properties.backend-info.generate_file_url' ), [], false ),
                [ 'Content-type' => 'application/json' ],
                json_encode( [ 'purchaseCode' => $purchase->code ] )
            ) )->wait( false );

            // release item.
            $purchase->status = config( 'constants.PURCHASES_RELEASED_STATUS' );
            $purchase->save();

            // return approved message
            return $this->sendResponse( [], 'Purchase processed successfully, file generated (admin user).', 202 );
        }
        else {

            // return payment init point link
            return $this->sendResponse( $purchase->payment_info[ 'init_point' ], 'Purchase processed successfully, payment init point link retrived.' );
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/peru_properties/generate_file",
     *     operationId="generatePropertiesFile",
     *     tags={"Peru Properties"},
     *     summary="Build the purchase files",
     *     @OA\Parameter(
     *         name="purchaseCode",
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
     *         description="Purchase not found."
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
            'purchaseCode'  => [ 'required', 'string' ],
        ] );

        // input
        $purchaseCode = $request->get( 'purchaseCode' );

        // get purchase
        $purchase = $this->purchaseRepository->findByField( 'code', $purchaseCode )->first();

        // validate purchase
        if ( empty( $purchase ) === true ) {
            \Log::info( 'Purchase not found.', $purchaseCode );

            return $this->sendError( 'Purchase not found.', [], 404 );
        }

        $filesInfo = [];

        // quantity of rows
        $rowsQuantity = $this->propertyRepository->countSelectedSearchedProperties( $purchase->search_id );

        // create json
        try {

            // get search
            $search = $this->searchRepository->findOrFail( $purchase->search_id );

            // get selected searched properties by user
            $selectedSearchedProperties = $this->propertyRepository->getSelectedSearchedProperties( $purchase->search_id );

            $filesInfo[] = $this->fileHandler->createAndUploadFile(
                array_merge( $search->toArray(), [ 'data' => $selectedSearchedProperties ] ),
                $rowsQuantity,
                $purchaseCode,
                'json'
            );

            // free memory
            unset( $search );
            unset( $selectedSearchedProperties );
            gc_collect_cycles();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        // create excel
        try {

            $filesInfo[] = $this->fileHandler->createAndUploadFile(
                [
                    'header'    => $this->propertyRepository->header,
                    'body'      => $this->propertyRepository->getSelectedSearchedPropertiesExcelFormat( $purchase->search_id ),
                ],
                $rowsQuantity,
                $purchaseCode,
                'xlsx'
            );

            gc_collect_cycles();
        } catch ( \Exception $e ) {
            return $this->sendError( $e->getMessage() );
        }

        $purchase->files_info = $filesInfo;
        $purchase->save();

        return $this->sendResponse( 'OK', 'Properties\' file generated successfully.' );
    }

    /**
     * @param   string $purchaseCode
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     * @throws  \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="api/peru_properties/purchases/{purchaseCode}/download",
     *     operationId="downloadPurchasedFile",
     *     tags={"Peru Properties"},
     *     summary="Download the export file",
     *     description="Returns the download link of file",
     *     @OA\Parameter(
     *         name="purchaseCode",
     *         description="code of purchase",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="download link retrived.",
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
     *         description="Access Denied."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Purchase not found."
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
    public function downloadPurchasedFile( $purchaseCode, Request $request )
    {
        $request->validate( [
            'format' => [ 'required', 'string', 'in:csv,xlsx,ods' ],
        ] );

        // input
        $format = $request->get( 'format' );

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

        $fileInfo = collect( $purchase->files_info )->filter( function ( $item, $index ) use ( $format ) {
            return $item[ 'type' ] === $format;
        } )->first();

        // get file
        $filePath = $this->fileHandler->downloadFile( $fileInfo[ 'bucket' ], $fileInfo[ 'name' ], false );

        // path to download the file
        $routeFilePath = route( 'downloadFiles', [ 'fileName' => basename( $filePath ) ] );

        return $this->sendResponse( $routeFilePath, 'Download link retrived.' );
    }
}
