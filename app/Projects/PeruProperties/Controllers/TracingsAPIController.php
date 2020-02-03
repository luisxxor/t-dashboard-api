<?php

namespace App\Projects\PeruProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use DateTime;
use App\Projects\PeruProperties\Repositories\TracingRepository;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use App\Projects\PeruProperties\Repositories\ClientRepository;
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
        TracingRepository $tracingRepo
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
            "address" => "Av. sadas Prolongacion los Tallanes",
            "bathrooms" => new Decimal128(2),
            "bedrooms" => new Decimal128(3),
            "build_area_m2" => new Decimal128(75.3),
            "comment_description" => " Vista Calle ",
            "comment_subtitle" => null,
            "dollars_price" => new Decimal128(83271),
            'image_list' => [ ],
            "latitude" => -5.164013014652,
            "link" => "https://urbania.pe/inmueble/proyecto-garden-360-piura-piura-edifica-2357/tipo-departamenasdasdto-12803",
            "longitude" => -80.628774213031,
            'metadata' => [
                [
                    "project_phase" => "EN CONSTRUCCIÓN",
                    "dollars_price" => new Decimal128(83271),
                    "others_price" => new Decimal128(280623),
                    "created_at" =>  new DateTime( 'now' )
                ]
            ],
            "others_price" => new Decimal128(280623),
            "parkings" => new Decimal128(1),
            "project_id" => 2357,
            "project_phase" => "EN CONSTRUCCIÓN",
            "property_name" => "Departamento Tipo A",
            "property_new" => true,
            "property_type_id" => "7595ad34a49bb70a2f11d82d787d3c3d",
            "publication_date" => new DateTime( 'now' ),
            "publication_type" => "venta",
            "region_id" => "e438e8a31a11c4552eca33477af32bac",
            "total_area_m2" => new Decimal128(75.3),
            'geo_location' => [
                "type" => "Point",
                "coordinates" => [ 
                    -80.628774213031, 
                    -5.164013014652
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
        $property = $this->propertyRepository->update( $propertyData, $id);

        return $this->sendResponse( $property, 'Tracing udpated successfully.' );
    }


}
