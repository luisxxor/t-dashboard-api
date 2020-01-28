<?php

namespace App\Projects\PeruProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use DateTime;
use App\Projects\PeruProperties\Repositories\TracingRepository;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

/**
 * Class TracingsAPIController
 * @package App\Projects\TracingProperties\Controllers
 */
class TracingsAPIController extends AppBaseController
{
    /**
     * @var SearchRepository
     */
    private $tracingRepository;
    private $propertyRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PropertyRepository $propertyRepo,
        TracingRepository $tracingRepo
    )
    {
        $this->propertyRepository = $propertyRepo;
        $this->tracingRepository = $tracingRepo;
    }
    /**
     * [index description]
     * @return \Illuminate\Http\JsonResponse]
     */
    public function index()
    {
        
    }

    public function tracingProperties( Request $request )
    {
        
        $request->validate( [
            'property_ids'      => [ 'required', 'array' ],
            'observation'       => [ 'nullable', 'string' ],
            'type_operation_id' => [ 'required', 'integer'],
            'nro_seg' => [ 'required', 'integer'],
        ] );

        // input
        $property_ids       = $request->get( 'property_ids' );
        $observation        = $request->get( 'observation' );
        $type_operation_id  = $request->get( 'type_operation_id' );
        $nro_seg            = $request->get( 'nro_seg' );

        // get user
        $user = auth()->user();

        // metadata data
        $tracingData = [
            'user_id' => $user->id,
            'property_ids' => $property_ids,
            'observation' => $observation,
            'type_operation_id' => $type_operation_id,
            'nro_seg' => $nro_seg,
            'created_at' => new DateTime( 'now' )
        ];

        // insert into 'tracings' collection
        $search = $this->tracingRepository->create( $tracingData );

        return $this->sendResponse( $search, 'Tracing retrieved successfully.' );
    }

    public function createProperties( Request $request )
    {
        
        $request->validate( [
            'latitude'   => [ 'required', 'numeric' ],
            'longitude'  => [ 'required', 'numeric' ],
            'address'  => [ 'required', 'string' ],
            'region_id'  => [ 'required', 'string' ],
            'property_type_id'  => [ 'required', 'string' ],
            'property_new'  => [ 'required', 'bool' ],
            'metadata'  => [ 'required', 'array' ],
        ] );

        // input
        $latitude       = $request->get( 'latitude' );
        $longitude        = $request->get( 'longitude' );
        $address  = $request->get( 'address' );
        $region_id  = $request->get( 'region_id' );
        $publication_type  = $request->get( 'publication_type' );
        $property_type_id  = $request->get( 'property_type_id' );
        $property_new  = $request->get( 'property_new' );
        $metadata  = $request->get( 'metadata' );

        // get user
        $user = auth()->user();

        // metadata data
        $propertyData = [
            'user_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'searched_properties' => "MMM",
            'total_area_m2' => null,
            'region_id' => $region_id,
            'publication_type' => $publication_type,
            'property_type_id' => $property_type_id,
            'property_new' => $property_new,
            'project_id' => null,
            'project_phase' => null,
            'property_name' => null,
            'metadata' => $metadata,
            'link' => 'tasin',
            'image_list' => [],
            'bathrooms' => null,
            'bedrooms' => null,
            'build_area_m2' => null,
            'comment_description' => null,
            'comment_subtitle' => null,
            'dollars_price' => null,
            'others_price' => null,
            'geo_location' => array(
                "type" => "Point", 
                "coordinates" => [ 
                $longitude, 
                $latitude
            ]),
            'created_at' => new DateTime( 'now' ),
            'updated_at' => new DateTime( 'now' ),
            'deleted_at' => null
        ];

        // insert into 'property' collection
        $property = $this->propertyRepository->create( $propertyData );

        return $this->sendResponse( $property, 'Tracing retrieved successfully.' );
    }
}
