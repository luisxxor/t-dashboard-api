<?php

namespace App\Http\Controllers\API\Subscriptions;

use App\Billing\PaymentGatewayContract;
use App\Http\Controllers\AppBaseController;
use App\Models\Subscriptions\PlanSubscription;
use App\Repositories\Dashboard\ReceiptRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class SubscriptionsPaymentAPIController
 * @package App\Http\Controllers\API\Subscriptions
 */
class SubscriptionsPaymentAPIController extends AppBaseController
{
    /**
     * @var  ReceiptRepository
     */
    private $receiptRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ReceiptRepository $receiptRepo )
    {
        $this->receiptRepository = $receiptRepo;
    }

    /**
     * @param \Illuminate\Http\Request              $request
     * @param \App\Billing\PaymentGatewayContract   $paymentGateway
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Post(
     *     path="/api/subscriptions/payments/process",
     *     operationId="pay-",
     *     tags={"Payments"},
     *     summary="Pay subscription.",
     *     @OA\Parameter(
     *         name="subscriptionId",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
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
     *         description="Subscription already released."
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
     *         description="Access Denied. User is not the owner of this subscription."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found."
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
            'subscriptionId'    => [ 'required', 'integer' ],
            'paymentType'       => [ 'required', 'string', Rule::in( array_values( config( 'constants.payment_gateways' ) ) ) ],
            'currency'          => [ 'required', 'string', Rule::in( array_values( config( 'constants.payment_currencies' ) ) ) ],
        ] );

        // input
        $subscriptionId = $request->get( 'subscriptionId' );
        $paymentType    = $request->get( 'paymentType' );
        $currency       = $request->get( 'currency' );

        // get subscription
        $subscription = PlanSubscription::find( $subscriptionId );

        // validate subscription
        if ( empty( $subscription ) === true ) {
            return $this->sendError( 'Subscription not found.', [], 404 );
        }

        // validate if the subscription belongs to the user
        if ( $subscription->user_id != auth()->user()->getKey() ) {
            throw new AuthorizationException;
        }

        // if subscription is already released
        if ( $subscription->status === config( 'constants.PLAN_SUBSCRIPTIONS.STATUS.RELEASED' ) ) {
            return $this->sendResponse( $subscription, 'Subscription already released.', 202 );
        }

        # validar el estatus del recibo, no de la suscripcion
        // if subscription is already processed but not released (to_pay or pending)
        if ( $subscription->status === config( 'constants.PLAN_SUBSCRIPTIONS.STATUS.PENDING' ) ) {
            return $this->sendResponse(
                $subscription->receipt->payment_info[ 'init_point' ],
                'Payment preference already exists. Init point returned successfully.'
            );
        }

        // amount
        $amount = $subscription->realPlan->price;

        // create receipt
        $receipt = $this->receiptRepository->findOrCreateByReceiptable( $subscription );

        try {
            // charge
            $paymentResult = $paymentGateway->charge(
                $receipt->code,
                $amount,
                [ 'title' => 'TASING SAC | Pago de subscription al ' . $subscription->realPlan->name . '' ]
            );
        } catch ( \Exception $e ) {
            \Log::error( 'Error at payment.', [ $subscription, $e->getMessage() ] );

            return $this->sendError( $e->getMessage(), [], 400 );
        }

        // save payment info in receipt
        $receipt->payment_type  = $paymentType;
        $receipt->currency      = $currency;
        $receipt->total_amount  = $amount;
        $receipt->total_tax     = 0.0;
        $receipt->payment_info  = $paymentResult;
        $receipt->save();

        // change subscription status
        $subscription->status = config( 'constants.PLAN_SUBSCRIPTIONS.STATUS.TO_PAY' );
        $subscription->save();

        return $this->sendResponse( $paymentResult[ 'init_point' ], 'Payment preference created. Init point returned successfully.' );
    }
}
