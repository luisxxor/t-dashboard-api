<?php

namespace Modules\DominicanaProperties\Http\Controllers;

use App\Lib\Handlers\GoogleStorageHandler;
use App\Repositories\Dashboard\OrderRepository;
use Illuminate\Http\Request;
use Modules\Common\Http\Controllers\PropertiesController as CommonPropertiesController;
use Modules\DominicanaProperties\Repositories\PropertyRepository;
use Modules\DominicanaProperties\Repositories\PropertyTypeRepository;
use Modules\DominicanaProperties\Repositories\PublicationTypeRepository;
use Modules\DominicanaProperties\Repositories\SearchRepository;

/**
 * Class PropertiesController
 * @package Modules\DominicanaProperties\Http\Controllers
 */
class PropertiesController extends CommonPropertiesController
{
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
     *     path="/api/do-properties/filters",
     *     operationId="filters",
     *     tags={"Dominicana Properties"},
     *     summary="Return the necessary data for filters.",
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
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=403, description="Access Denied. User has no access to this proyect." ),
     *     security={ { "": {} } }
     * )
     */
    public function filters( PublicationTypeRepository $publicationTypeRepo )
    {
        // select property types
        $propertyTypes = $this->propertyTypeRepository->distinct( 'name' );
        $propertyTypes = array_column( $propertyTypes->toArray(), 0 );
        $propertyTypes = array_map( function ( $value ) {
            return [
                'text' => $value,
                'value' => $value,
            ];
        }, $propertyTypes );

        // select publication types
        $publicationTypes = $publicationTypeRepo->distinct( 'name' );
        $publicationTypes = array_column( $publicationTypes->toArray(), 0 );
        $publicationTypes = array_map( function ( $value ) {
            return [
                'text' => $value,
                'value' => $value,
            ];
        }, $publicationTypes );

        // sort
        sort( $propertyTypes );
        sort( $publicationTypes );

        $data = [
            config( 'multi-api.do-properties.constants.FILTER_FIELD_PROPERTY_TYPE' ) => $propertyTypes,
            config( 'multi-api.do-properties.constants.FILTER_FIELD_PUBLICATION_TYPE' ) => $publicationTypes,
        ];

        return $this->sendResponse( $data, 'Data retrieved.' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/do-properties/search",
     *     operationId="searchProperties",
     *     tags={"Dominicana Properties"},
     *     summary="Return the properties that math with given filters",
     *     @OA\Parameter( name="vertices", required=true, in="query", @OA\Schema( type="array", @OA\Items() ) ),
     *     @OA\Parameter( name="filters", required=false, in="query", @OA\Schema( type="array", @OA\Items() ) ),
     *     @OA\Parameter( name="lat", required=true, in="query", @OA\Schema( type="double" ) ),
     *     @OA\Parameter( name="lng", required=true, in="query", @OA\Schema( type="double" ) ),
     *     @OA\Parameter( name="address", required=false, in="query", @OA\Schema( type="string" ) ),
     *     @OA\Parameter( name="perpage", required=true, in="query", @OA\Schema( type="integer" ) ),
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
     *     @OA\Response( response=204, description="The request has been successfully completed but your answer has no content" ),
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=403, description="Access Denied. User has no access to this proyect." ),
     *     @OA\Response( response=422, description="The given data was invalid." ),
     *     security={ { "": {} } }
     * )
     */
    public function searchProperties( Request $request )
    {
        return parent::searchProperties( $request );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/do-properties/count",
     *     operationId="countSearch",
     *     tags={"Dominicana Properties"},
     *     summary="Return the search count",
     *     @OA\Parameter( name="searchId", required=true, in="query", @OA\Schema( type="string" ) ),
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
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=403, description="Access Denied. User has no access to this proyect." ),
     *     @OA\Response( response=404, description="Data not found." ),
     *     @OA\Response( response=422, description="The given data was invalid." ),
     *     security={ { "": {} } }
     * )
     */
    public function countSearch( Request $request )
    {
        return parent::countSearch( $request );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/do-properties/paginate",
     *     operationId="paginateSearch",
     *     tags={"Dominicana Properties"},
     *     summary="Return the properties that math with given search id",
     *     @OA\Parameter( name="searchId",required=true,in="query",@OA\Schema( type="string" ) ),
     *     @OA\Parameter( name="lastItem",required=true,in="query",@OA\Schema( type="array", @OA\Items() ) ),
     *     @OA\Parameter( name="perpage",required=true,in="query",@OA\Schema( type="integer" ) ),
     *     @OA\Parameter( name="field",required=false,in="query",@OA\Schema( type="string" ) ),
     *     @OA\Parameter( name="sort",required=false,in="query",@OA\Schema( type="integer" ) ),
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
     *     @OA\Response( response=204, description="The request has been successfully completed but your answer has no content" ),
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=403, description="Access Denied. User has no access to this proyect." ),
     *     @OA\Response( response=404, description="Order not found." ),
     *     @OA\Response( response=422, description="The given data was invalid." ),
     *     security={ { "": {} } }
     * )
     */
    public function paginateSearch( Request $request )
    {
        return parent::paginateSearch( $request );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/do-properties/order",
     *     operationId="order",
     *     tags={"Dominicana Properties"},
     *     summary="Order items",
     *     description="Create order in case it does not, and update the 'ids' value of search",
     *     @OA\Parameter( name="searchId", required=true, in="query", @OA\Schema( type="string" ) ),
     *     @OA\Parameter( name="ids", required=true, in="query", @OA\Schema( type="array", @OA\Items() ) ),
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
     *     @OA\Response( response=201, description="Ordered successfully, file generated." ),
     *     @OA\Response( response=400, description="Bad Request. | The order is already created and has already been processed." ),
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=402, description="Cannot create order because user has no subscription or is expired." ),
     *     @OA\Response( response=403, description="Access Denied. User has no access to this proyect." ),
     *     @OA\Response( response=404, description="Search not found." ),
     *     @OA\Response( response=409, description="User subscription has exhausted the download quota." ),
     *     @OA\Response( response=422, description="The given data was invalid." ),
     *     security={ { "": {} } }
     * )
     */
    public function order( Request $request )
    {
        return parent::order( $request );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/do-properties/generate_file",
     *     operationId="generatePropertiesFile",
     *     tags={"Dominicana Properties"},
     *     summary="Build the order files",
     *     @OA\Parameter( name="orderCode", required=true, in="query", @OA\Schema( type="string" ) ),
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
     *     @OA\Response( response=401, description="Unauthenticated." ),
     *     @OA\Response( response=404, description="Order not found." ),
     *     @OA\Response( response=422, description="The given data was invalid." )
     * )
     */
    public function generatePropertiesFile( Request $request )
    {
        return parent::generatePropertiesFile( $request );
    }
}
