<?php

namespace App\Http\Controllers\API\IPN;

use App\Http\Controllers\AppBaseController;
use App\Lib\Handlers\MercadoPagoHandler;
use App\Repositories\Dashboard\ReceiptRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

/**
 * Class MercadopagoAPIController
 * @package App\Http\Controllers\API\IPN
 */
class MercadopagoAPIController extends AppBaseController
{
    /**
     * @var ReceiptRepository
     */
    private $receiptRepository;

    /**
     * @var MercadoPagoHandler
     */
    private $mercadoPago;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ReceiptRepository $receiptRepo )
    {
        $this->receiptRepository = $receiptRepo;
        $this->mercadoPago = new MercadoPagoHandler( config( 'services.mercadopago.access_token' ) );
    }

    /**
     * POST /api/dashboard/notifications/mp
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
        $topic          = $request->get( 'topic' );
        $notificationId = $request->get( 'id' );

        Log::debug( [ 'debug' => 'MercadopagoAPIController', 'id' => $request->get( 'id' ), 'topic' => $request->get( 'topic' ) ] );

        // get the merchantOrder
        switch( $topic ) {
            case 'payment':
                $payment = $this->mercadoPago->getPayment( $notificationId );

                // get the payment and the corresponding merchantOrder reported by the IPN.
                $merchantOrderInfo = $this->mercadoPago->getMerchantOrder( $payment->order->id ?? null );

                break;

            case 'merchant_order':
                $merchantOrderInfo = $this->mercadoPago->getMerchantOrder( $notificationId );

                break;
        }

        // si el existe informacion del pago
        if ( isset( $merchantOrderInfo ) === true && $merchantOrderInfo !== null ) {

            // get external reference id
            $externalReferenceId = $merchantOrderInfo->external_reference;

            // get receipt
            $receipt = $this->receiptRepository->findByField( 'code', $externalReferenceId )->first();

            // si el codigo externo corresponde con una compra del sistema
            if ( empty( $receipt ) === false ) {

                // link payment info
                $receipt->fill( [ 'payment_info->payment' => $merchantOrderInfo->getAttributes() ] );

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
                    $receipt->setReleasedStatus();
                }
                else {
                    // not paid yet. Do not release the item.
                    if ( $receipt->isReleasedStatus() === false ) {
                        $receipt->setPendingStatus();
                    }
                }
            }
        }

        return response( 'OK', 201 );
    }
}
