<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\MercadoPagoHandler;
use App\Repositories\Dashboard\PurchaseRepository;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Response;

/**
 * Class MercadoPagoAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class MercadoPagoAPIController extends AppBaseController
{
    /**
    * @var  PurchaseRepository
    */
    private $purchaseRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( PurchaseRepository $purchaseRepo )
    {
        $this->purchaseRepository = $purchaseRepo;
    }

    /**
     * POST /dashboard/notifications/mp
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

        $mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );

        // merchantOrder
        $merchantOrderInfo = null;

        // get the merchantOrder
        switch( $mp_topic ) {
            case 'payment':
                $payment = $mercadoPago->getPayment( $mp_notification_id );

                // get the payment and the corresponding merchantOrder reported by the IPN.
                $merchantOrderInfo = $mercadoPago->getMerchantOrder( $payment->order->id ?? null );

                break;

            case 'merchant_order':
                $merchantOrderInfo = $mercadoPago->getMerchantOrder( $mp_notification_id );

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

                // link payment info
                $purchase->fill( [ 'payment_info->payment' => $merchantOrderInfo->getAttributes() ] );

                // calculate the payment's transaction amount (it should be only one)
                $paidAmount = 0;
                foreach ( $merchantOrderInfo->payments as $payment ) {
                    // validate the status
                    if ( $payment->status === 'approved' ) {
                        $paidAmount += $payment->transaction_amount;
                    }
                }

                // if the payment's transaction amount is equal (or bigger) than the
                // merchantOrder's amount then release item
                if ( $paidAmount >= $merchantOrderInfo->total_amount ) {

                    // generate files request
                    $guzzleClient = new GuzzleClient( [ 'base_uri' => url( '/' ), 'timeout' => 30.0 ] );
                    $guzzleClient->sendAsync( new GuzzleRequest(
                        'GET',
                        route( 'api.' . config( 'multi-api.' . $purchase->project . '.backend-info.generate_file_url' ), [], false ),
                        [ 'Content-type' => 'application/json' ],
                        json_encode( [ 'purchaseCode' => $purchase->code ] )
                    ) )->wait( false );

                    // release item.
                    $purchase->status = config( 'constants.PURCHASES_RELEASED_STATUS' );
                    $purchase->save();
                }
                else {
                    // not paid yet. Do not release the item.
                    if ( $purchase->status !== config( 'constants.PURCHASES_RELEASED_STATUS' ) ) {
                        $purchase->status = config( 'constants.PURCHASES_PENDING_STATUS' );
                        $purchase->save();
                    }
                }
            }
        }

        return response( 'OK', 201 );
    }
}
