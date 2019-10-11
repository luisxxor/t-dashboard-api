<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\ProjectRepository;
use App\Repositories\Dashboard\PurchaseFileRepository;
use App\Repositories\Dashboard\PurchaseRepository;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\MerchantOrder as MercadoPagoMerchantOrder;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\SDK as MercadoPagoSDK;
use Response;

/**
 * Class PurchasesAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class PurchasesAPIController extends AppBaseController
{
    /**
    * @var  PurchaseRepository
    */
    private $purchaseRepository;

    /**
     * @var  PurchaseFileRepository
     */
    private $purchaseFileRepository;

    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PurchaseRepository $purchaseRepo,
        PurchaseFileRepository $purchaseFileRepo,
        ProjectRepository $projectRepo )
    {
        $this->purchaseRepository = $purchaseRepo;
        $this->purchaseFileRepository = $purchaseFileRepo;
        $this->projectRepository = $projectRepo;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/purchases/purchase_files",
     *     operationId="index",
     *     tags={"Purchases"},
     *     summary="Display a listing of the user's purchase files",
     *     @OA\Parameter(
     *         name="project",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase files retrived successfully.",
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
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function index( Request $request )
    {
        $request->validate( [
            'project' => 'required|string',
        ] );

        // formato del archivo
        $projectCode = $request->get( 'project' );

        // validate project
        $project = $this->projectRepository->findByField( 'code', $projectCode );

        if ( empty( $project ) === true || $project->isEmpty() === true ) {
            return $this->sendError( 'Project not found.', [], 404 );
        }

        $purchaseFiles = auth()->user()->purchaseFiles()->with(  'purchase' )->get();

        // solo las compras concretadas y del project actual
        $purchaseFiles = $purchaseFiles->filter( function ( $item, $index ) use ( $projectCode ) {
            return $item->mp_status === 'approved' && $item[ 'purchase' ]->project === $projectCode;
        } );

        return $this->sendResponse( $purchaseFiles, 'Purchase files retrived successfully.' );
    }

    /**
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/purchases/purchase_files/{id}/records",
     *     operationId="show",
     *     tags={"Purchases"},
     *     summary="Display the specified user's purchase file",
     *     @OA\Parameter(
     *         name="id",
     *         description="id of purchase file",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase file retrived successfully.",
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
     *         response=404,
     *         description="Purchase File not found."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function show( $id )
    {
        $purchaseFile = $this->purchaseFileRepository->find( $id );

        if ( empty( $purchaseFile ) === true ) {
            \Log::info( 'Purchase File not found.', [ $purchaseFile ] );

            return $this->sendError( 'Purchase File not found.', [], 404 );
        }

        if ( $purchaseFile[ 'purchase' ]->user_id !== auth()->user()->id ) {
            \Log::info( 'Access denied.', [ auth()->user(), $purchaseFile ] );

            return $this->sendError( 'Purchase File not found for this user.', [], 404 );
        }

        // obtener json con la data
        $json = $this->purchaseFileRepository->getJson( $purchaseFile->file_path, $purchaseFile->file_name );

        // solo el body de la data
        $data = $json[ 'data' ][ 'body' ];

        $imageLists = $json[ 'metadata' ][ 'image_list' ];

        // asocuar imagenes a cada propiedad
        foreach ( $imageLists as $key => $item ) {
            $data[ $key ][ 'image_list' ] = $item;
        }

        return $this->sendResponse( $data, 'Purchase file retrived successfully.' );
    }

    /**
     * Build the export file with the given properties (ids) and the given
     * format (format). Returns the MercadoPago link.
     * POST /admin/properties/mercadoPago
     *
     * @param  \Illuminate\Http\Request  $request
     * @return  Response
     */
    public function ipnNotification( Request $request )
    {
        if ( !isset( $_GET[ 'id' ], $_GET[ 'topic' ] ) || !ctype_digit( $_GET[ 'id' ] ) ) {
            abort( 404 );
        }

        // request values
        $mp_topic           = $request->get( 'topic' );
        $mp_notification_id = $request->get( 'id' );

        \Log::info( 'request values' );
        \Log::debug( [ 'id' => $request->get( 'id' ), 'topic' => $request->get( 'topic' ) ] );

        // setAccessToken
        MercadoPagoSDK::setAccessToken( 'APP_USR-5002297790161278-031119-1868dce80b89a7737bdf32dff71f83a8-415292930' );

        // merchantOrder
        $merchantOrderInfo = null;

        // get the merchantOrder
        switch( $mp_topic ) {
            case 'payment':
                $payment = MercadoPagoPayment::find_by_id( $mp_notification_id );

                // Get the payment and the corresponding merchantOrder reported by the IPN.
                $merchantOrderInfo = MercadoPagoMerchantOrder::find_by_id( $payment->order->id ?? null );

                break;
            case 'merchant_order':
                $merchantOrderInfo = MercadoPagoMerchantOrder::find_by_id( $mp_notification_id );

                break;
        }
        // si el existe informacion del pago
        if ( $merchantOrderInfo !== null ) {

            // get external reference id
            $externalReferenceId = $merchantOrderInfo->external_reference;

            // get purchase
            $purchase = $this->purchaseRepository->findByField( 'code', $externalReferenceId )->first();

            // si el codigo externo corresponde con una compra del sistema
            if ( empty( $purchase ) === false ) {

                // link notification id
                $purchase->mp_notification_id = $mp_notification_id;

                // Calculate the payment's transaction amount
                $paidAmount = 0;
                foreach ( $merchantOrderInfo->payments as $payment ) {
                    // validate the status
                    if ( $payment->status === 'approved' ) {
                        $paidAmount += $payment->transaction_amount;
                    }
                }

                // If the payment's transaction amount is equal (or bigger) than the
                // merchantOrder's amount you can release your items
                if ( $paidAmount >= $merchantOrderInfo->total_amount ) {
                    // Totally paid. Release your item.
                    $purchase->mp_status = 'approved';

                    $generateFileUrl = route( config( 'multi-api.' . $purchase->project . '.backend-info.generate_file_url_full' ), [], false );

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
                }
                else {
                    // Not paid yet. Do not release your item.
                }

                // save purchase
                $purchase->save();
            }
        }

        return response( 'OK', 201 );
    }
}
