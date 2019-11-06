<?php

namespace App\Projects\PeruProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Projects\PeruProperties\Lib\PropertyClass;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use App\Projects\PeruProperties\Repositories\PropertyTypeRepository;
use App\Projects\PeruProperties\Repositories\SearchRepository;
use App\Repositories\Dashboard\PurchaseFileRepository;
use App\Repositories\Dashboard\PurchaseRepository;
use DB;
use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Class PropertiesAPIController
 * @package App\Projects\PeruProperties\Controllers
 */
class PropertiesAPIController extends AppBaseController
{
    /**
     * @var PropertyClass
     */
    private $propertyClass;

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
     * @var  PurchaseFileRepository
     */
    private $purchaseFileRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PropertyTypeRepository $propertyTypeRepo,
        PropertyRepository $propertyRepo,
        SearchRepository $searchRepo,
        PurchaseRepository $purchaseRepo,
        PurchaseFileRepository $purchaseFileRepo )
    {
        $this->propertyClass = new PropertyClass();
        $this->propertyTypeRepository = $propertyTypeRepo;
        $this->propertyRepository = $propertyRepo;
        $this->searchRepository = $searchRepo;
        $this->purchaseRepository = $purchaseRepo;
        $this->purchaseFileRepository = $purchaseFileRepo;
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
     *         required=true,
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
            'address'   => [ 'required', 'string' ],
            'perpage'   => [ 'required', 'integer', 'min:10', 'max:500'], #
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
        // this will create the temp collection (named as the search id)
        // and store in the matched properties
        $this->propertyRepository->storeTempProperties( $search );

        // paginate data (default)
        $page   = 1;
        $field  = 'publication_date';
        $sort   = -1;

        // construct and execute query
        $results = $this->propertyRepository->getTempProperties( $search->_id, compact( 'page', 'perpage', 'field', 'sort' ) );

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
     *     summary="Return the properties in given page that math with given search id",
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
     *         required=true,
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
        $results = $this->propertyRepository->getTempProperties( $searchId, compact( 'page', 'perpage', 'field', 'sort' ) );

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
     *     summary="Create the Purchase and purchaseFile models",
     *     description="Procces purchase and returns the MercadoPago link (if no admin)",
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
     *         description="Purchase processed successfully, mercadopago link sended.",
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

        // get user id
        $user = auth()->user();

        // get user rol
        $isAdmin = $user->hasRole( 'admin' );

        // get selected ids by user
        if ( $selectAll === true ) {
            $searchUpdate = [
                'selected_properties' => [ '*' ]
            ];

            $total = DB::connection( 'peru_properties' )->collection( $searchId )->count();
        }
        else {
            $searchUpdate = [
                'selected_properties' => $ids
            ];

            $total = count( $ids );
        }

        // update the search to save selected ids by user
        $search = $this->searchRepository->update( $searchUpdate, $searchId );

        // save purchase
        $purchase = $this->purchaseRepository->generate( [
            'user_id'   => $user->id,
            'status'    => 1,
            'search_id' => $search->_id,
            'project'   => config( 'multi-api.pe-properties.backend-info.code' ),
            'files' => [
                [
                    'title'         => 'Export Tasing Properties',
                    'row_quantity'  => $total
                ]
            ]
        ] );

        // if admin generate file, else return mercadopago link
        if ( $isAdmin === true ) {
            $generateFileUrl = route( config( 'multi-api.pe-properties.backend-info.generate_file_url_full' ), [], false );

            // guzzle client
            $guzzleClient = new GuzzleClient( [
                'base_uri' => url( '/' ) . '/',
                'timeout' => 30.0,
            ] );

            // Create a PSR-7 request object to send
            $headers = [ 'Content-type' => 'application/json' ];
            $body = [ 'purchaseId' => $purchase->id ];
            $guzzleRequest = new GuzzleRequest( 'GET', $generateFileUrl, $headers, json_encode( $body ) );
            $promise = $guzzleClient->sendAsync( $guzzleRequest );
            $promise->wait( false );

            // status approved
            $purchase->mp_status = 'approved';
            $purchase->save();

            // return approved message
            return $this->sendError( 'Purchase processed successfully, file generated (admin user).', [], 202 );
        }
        else {
            // return mercadopago link
            return $this->sendResponse( $purchase->mp_init_point, 'Purchase processed successfully, mercadopago link retrived.' );
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
     *     summary="Build the export file with the given search item (mongodb document)",
     *     @OA\Parameter(
     *         name="purchaseId",
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
            'purchaseId'  => [ 'required', 'numeric' ],
        ] );

        // input
        $purchaseId = $request->get( 'purchaseId' );

        // get purchase
        $purchase = $this->purchaseRepository->find( $purchaseId );

        if ( empty( $purchase ) === true ) {
            \Log::info( 'Purchase not found.', $purchaseId );

            return $this->sendError( 'Purchase not found.', [], 404 );
        }

        // create purchase json
        $this->propertyClass->createPurchaseJson( $purchase );

        // response OK
        return $this->sendResponse( 'OK', 'Properties\' file generated successfully.' );
    }

    /**
     * @param   int $id
     * @param   \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\JsonResponse
     * @throws  \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Post(
     *     path="api/peru_properties/purchase_files/{id}/export",
     *     operationId="exportPurchasedFile",
     *     tags={"Peru Properties"},
     *     summary="Build the export file",
     *     description="Build the export file with the json of purchased properties and the given format (format). Returns the download's link of file",
     *     @OA\Parameter(
     *         name="id",
     *         description="id of purchase file",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
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
     *         description="Purchase file exported successfully, download link retrived.",
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
     *         description="Purchase File not found."
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
    public function exportPurchasedFile( $id, Request $request )
    {
        $request->validate( [
            'format' => [ 'required', 'string', 'in:csv,xlsx,ods' ],
        ] );

        $purchaseFile = $this->purchaseFileRepository->find( $id );

        if ( empty( $purchaseFile ) === true ) {
            \Log::info( 'Purchase File not found.', $purchaseFile );

            return $this->sendError( 'Purchase File not found.', [], 404 );
        }

        // validar si el archivo pertenece al usuario
        if ( $purchaseFile->purchase->user->id != auth()->user()->getKey() ) {
            throw new AuthorizationException;
        }

        // formato del archivo
        $format = $request->get( 'format' );

        // obtener json con la data
        $json = $this->purchaseFileRepository->getJson( $purchaseFile->file_path, $purchaseFile->file_name );

        // creacion del archivo a exportar
        $exportedFileData = $this->propertyClass->createExportFile( $json[ 'data' ][ 'header' ], $json[ 'data' ][ 'body' ], $format );

        // Ruta para descargar el archivo
        $routeFilePath = route( 'downloadFiles', [ 'fileName' => $exportedFileData[ 'name' ] ] );

        return $this->sendResponse( $routeFilePath, 'Purchase file exported successfully, download link retrived.' );
    }
}
