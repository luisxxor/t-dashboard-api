<?php

namespace App\Projects\ChileProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use DateTime;
use App\Projects\ChileProperties\Repositories\TracingRepository;
use App\Projects\ChileProperties\Repositories\PropertyRepository;
use App\Projects\ChileProperties\Repositories\ClientRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use MongoDB\BSON\Decimal128;

/**
 * Class TracingsAPIController
 * @package App\Projects\TracingProperties\Controllers
 */
class TracingsAPIController extends AppBaseController
{
    /**
     * @var Var Repository
     */
    private $tracingRepository;
    private $propertyRepository;
    private $clientRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PropertyRepository $propertyRepo,
        TracingRepository $tracingRepo,
        ClientRepository $clientRepo
    )
    {
        $this->propertyRepository = $propertyRepo;
        $this->tracingRepository = $tracingRepo;
        $this->clientRepository = $clientRepo;
    }
    /**
     * [index description]
     * @return \Illuminate\Http\JsonResponse]
     */
    public function index()
    {
        
    }


    /**
     * TRANCING
     */
    
    public function createTracing( Request $request )
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
        $trancing = $this->tracingRepository->create( $tracingData );

        return $this->sendResponse( $search, 'Tracing retrieved successfully.' );
    }

    public function editTracing( $id )
    {
        $trancing = $this->tracingRepository->findOrFail( $id );

        return $this->sendResponse( $trancing, 'Show tracing successfully' );
    }    

    public function updateTracing( Request $request, $id )
    {
        $trancing = $this->tracingRepository->findOrFail( $id );

        // update into 'tracings' collection
        $trancing = $this->tracingRepository->update( $request->all(), $trancing->id);

        return $this->sendResponse( $trancing, 'Tracing udpated successfully.' );
    }    


    public function deleteTracing( $id )
    {
        $trancing = $this->tracingRepository->findOrFail( $id );

        // delete into 'tracings' collection
        $trancing = $this->tracingRepository->delete( $trancing->id);

        return $this->sendResponse( $trancing, 'Tracing delete successfully.' );
    }


    /**
     * Client
    */
    
    public function createClient( Request $request )
    {
    
        $request->validate( [
            'personal_id'      => [ 'required', 'string' ],
            'first_name'       => [ 'required', 'string' ],
            'last_name'       => [ 'required', 'string' ],
            'phone'       => [ 'required', 'string' ],
            'email'       => [ 'required', 'string' ],
        ] );

        // input
        $personal_id       = $request->get( 'personal_id' );
        $first_name        = $request->get( 'first_name' );
        $last_name         = $request->get( 'last_name' );
        $phone             = $request->get( 'phone' );
        $email             = $request->get( 'email' );
        $executive         = $request->get( 'executive' );
        $email_executive   = $request->get( 'email_executive' );

        // get user
        $user = auth()->user();

        // metadata data
        $clientData = [
            'personal_id' => $personal_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'executive' => $executive,
            'email_executive' => $email_executive,
            'user_id' => $user->id,
            'created_at' => new DateTime( 'now' )
        ];

        // insert into 'clients' collection
        $client = $this->clientRepository->create( $clientData );

        return $this->sendResponse( $search, 'Client retrieved successfully.' );
    }

    public function editClient( $id )
    {
        $client = $this->clientRepository->findOrFail( $id );

        return $this->sendResponse( $client, 'Show client successfully' );
    }    

    public function updateClient( Request $request, $id )
    {
        $client = $this->clientRepository->findOrFail( $id );

        // update into 'clients' collection
        $client = $this->clientRepository->update( $request->all(), $client->id);

        return $this->sendResponse( $client, 'Client udpated successfully.' );
    }    


    public function deleteClient( $id )
    {
        $client = $this->clientRepository->findOrFail( $id );

        // delete into 'clients' collection
        $client = $this->clientRepository->delete( $client->id);

        return $this->sendResponse( $client, 'Client delete successfully.' );
    }

    /**
     * PROPERTIES
     */
    public function initPoint( Request $request )
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
        $properties = $this->propertyRepository->searchPropertiesToTracing( $lat, $lng, $maxDistance );

        return $this->sendResponse( array('count' => count($properties), 'properties' => $properties ), 'Success.' );
    }


    public function createProperties( Request $request )
    {
        
        $request->validate( [
            'latitude'   => [ 'required', 'numeric' ],
            'longitude'  => [ 'required', 'numeric' ],
            'address'  => [ 'required', 'string' ],
            'link'  => [ 'required', 'string' ]
        ] );

        // input
        $latitude       = $request->get( 'latitude' );
        $longitude        = $request->get( 'longitude' );
        $address  = $request->get( 'address' );
        $link  = $request->get( 'link' );

        // get user
        $user = auth()->user();

        // metadata data

        $propertyData = [
            "address"      => $address,
            "latitude"     => $latitude ,
            "link"         => $link,
            "longitude"    => $longitude,
            'geo_location' => [
                "type"         => "Point",
                "coordinates"  => [ 
                    $longitude, 
                    $latitude 
                    ]
            ],
        ];


        // insert into 'property' collection
        $property = $this->propertyRepository->create( $propertyData );

        return $this->sendResponse( $property, 'Tracing retrieved successfully.' );
    }    

    public function updateProperties( Request $request, $id )
    {
        
        // update into 'property' collection
        $property = $this->propertyRepository->update( $request->all(), $id);

        return $this->sendResponse( $property, 'Tracing udpated successfully.' );
    }


}
