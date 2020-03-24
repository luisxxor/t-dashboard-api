<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Billing\MercadopagoPaymentGateway;
use App\Billing\PaymentGatewayContract;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\OrderRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class OrdersPaymentAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class OrdersPaymentAPIController extends AppBaseController
{
    /**
     * @const float
     */
    const BASE_PRICE = 35.0;

    /**
     * @const int
     */
    const BASE_QUANTITY = 15;

    /**
     * @const float
     */
    const ADDITIONAL_PRICE = 1;

    /**
     * @var  OrderRepository
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
     * @param \Illuminate\Http\Request              $request
     * @param \App\Billing\PaymentGatewayContract   $paymentGateway
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Post(
     *     path="/api/dashboard/payments/process",
     *     operationId="pay",
     *     tags={"Payments"},
     *     summary="Pay order.",
     *     @OA\Parameter(
     *         name="orderCode",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentType",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="currency",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Init point returned successfully.",
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
     *         response=202,
     *         description="Order already released."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request."
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
     *         description="Order not found."
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
    public function pay( Request $request, PaymentGatewayContract $paymentGateway )
    {
        $request->validate( [
            'orderCode'         => [ 'required', 'string' ],
            'paymentType'       => [ 'required', 'string', Rule::in( array_values( config( 'constants.payment_gateways' ) ) ) ],
            'currency'          => [ 'required', 'string', Rule::in( array_values( config( 'constants.payment_currencies' ) ) ) ],
        ] );

        // input
        $orderCode      = $request->get( 'orderCode' );
        $paymentType    = $request->get( 'paymentType' );
        $currency       = $request->get( 'currency' );

        // get order
        $order = $this->orderRepository->findByField( 'code', $orderCode )->first();

        # TODO: aqui hay que verificar que la search aun exista en mongodb,
        #       porque si no existe el usuario va a pagar y no se va a generar nada

        // validate order
        if ( empty( $order ) === true ) {
            \Log::info( 'Order not found.', [ $orderCode ] );

            return $this->sendError( 'Order not found.', [], 404 );
        }

        // validate if the order belongs to the user
        if ( $order->user_id != auth()->user()->getKey() ) {
            throw new AuthorizationException;
        }

        // if order is already released
        if ( $order->status === config( 'constants.ORDERS_RELEASED_STATUS' ) ) {

            return $this->sendResponse( $order, 'Order already released.', 202 );
        }

        // if order is already processed but not released (to_pay or pending)
        if ( $order->status === config( 'constants.ORDERS_TO_PAY_STATUS' )
            || $order->status === config( 'constants.ORDERS_PENDING_STATUS' ) ) {

            return $this->sendResponse(
                $order->payment_info[ 'init_point' ],
                'Payment preference already exists. Init point returned successfully.'
            );
        }

        // amount
        $amount = $this->calculateAmount( $order->total_rows_quantity );

        try {
            // charge
            $paymentResult = $paymentGateway->charge( $orderCode, $amount, $order->total_rows_quantity );
        } catch ( \Exception $e ) {
            \Log::error( 'Error at payment.', [ $order, $e->getMessage() ] );

            return $this->sendError( $e->getMessage(), 400 );
        }

        // save payment info
        $order->payment_type    = $paymentType;
        $order->currency        = $currency;
        $order->total_amount    = $amount;
        $order->total_tax       = 0.0;
        $order->payment_info    = $paymentResult;
        $order->status          = config( 'constants.ORDERS_TO_PAY_STATUS' );
        $order->save();

        return $this->sendResponse( $paymentResult[ 'init_point' ], 'Payment preference created. Init point returned successfully.' );
    }

    /**
     * Calculate the amount with the rows quantity.
     *
     * @param int $rowQuantity
     *
     * @return float
     */
    protected function calculateAmount( int $rowQuantity ): float
    {
        $amount = self::BASE_PRICE;

        // if the number of records is greater than the base price
        if ( $rowQuantity > self::BASE_QUANTITY ) {

            // get the number of additional records
            $additionalQuantity = $rowQuantity - self::BASE_QUANTITY;

            // add the additional price
            $amount += $additionalQuantity * self::ADDITIONAL_PRICE;
        }

        return $amount;
    }
}
