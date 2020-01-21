<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\MercadoPagoHandler;
use App\Repositories\Dashboard\OrderRepository;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Response;

/**
 * Class MercadopagoAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class MercadopagoAPIController extends AppBaseController
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( OrderRepository $orderRepo )
    {
        $this->orderRepository = $orderRepo;
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
        $topic           = $request->get( 'topic' );
        $notificationId = $request->get( 'id' );

        \Log::info( 'request values' );
        \Log::debug( [ 'id' => $request->get( 'id' ), 'topic' => $request->get( 'topic' ) ] );

        $mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );

        // merchantOrder
        $merchantOrderInfo = null;

        // get the merchantOrder
        switch( $topic ) {
            case 'payment':
                $payment = $mercadoPago->getPayment( $notificationId );

                // get the payment and the corresponding merchantOrder reported by the IPN.
                $merchantOrderInfo = $mercadoPago->getMerchantOrder( $payment->order->id ?? null );

                break;

            case 'merchant_order':
                $merchantOrderInfo = $mercadoPago->getMerchantOrder( $notificationId );

                break;
        }

        // si el existe informacion del pago
        if ( $merchantOrderInfo !== null ) {

            // get external reference id
            $externalReferenceId = $merchantOrderInfo->external_reference;

            // get order
            $order = $this->orderRepository->findByField( 'code', $externalReferenceId )->first();

            // si el codigo externo corresponde con una compra del sistema
            if ( empty( $order ) === false ) {

                // link payment info
                $order->fill( [ 'payment_info->payment' => $merchantOrderInfo->getAttributes() ] );

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
                        route( 'api.' . config( 'multi-api.' . $order->project . '.backend-info.generate_file_url' ), [], false ),
                        [ 'Content-type' => 'application/json' ],
                        json_encode( [ 'orderCode' => $order->code ] )
                    ) )->wait( false );

                    // release item.
                    $order->status = config( 'constants.ORDERS_RELEASED_STATUS' );
                    $order->save();
                }
                else {
                    // not paid yet. Do not release the item.
                    if ( $order->status !== config( 'constants.ORDERS_RELEASED_STATUS' ) ) {
                        $order->status = config( 'constants.ORDERS_PENDING_STATUS' );
                        $order->save();
                    }
                }
            }
        }

        return response( 'OK', 201 );
    }
}
