<?php

namespace App\Projects\TracingProperties\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Projects\PeruProperties\Repositories\SearchRepository;
use DateTime;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SearchRepository $tracingRepo)
    {
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

}
