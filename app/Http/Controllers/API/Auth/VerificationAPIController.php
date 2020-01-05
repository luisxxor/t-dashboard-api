<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationAPIController extends AppBaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware( 'auth:api' );
        $this->middleware( 'signed' )->only( 'verify' );
        $this->middleware( 'throttle:6,1' )->only( 'verify', 'resend' );
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/api/email/verify",
     *     operationId="show",
     *     tags={"Auth"},
     *     summary="Get the email verification status (true or false)",
     *     @OA\Response(
     *         response=200,
     *         description="Verification status returned.",
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
     *                     property="has_verified_email",
     *                     type="boolean",
     *                 )
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
    public function show()
    {
        $hasVerifiedEmail = auth()->user()->hasVerifiedEmail();

        return $this->sendResponse( [ 'has_verified_email' => $hasVerifiedEmail ], 'Verification status returned.' );
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     operationId="verify",
     *     tags={"Auth"},
     *     summary="Mark the authenticated user's email address as verified",
     *     @OA\Parameter(
     *         name="id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="expires",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="signature",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verified successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Already verified."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid signature; or user logged isn't the same as user looking for verification."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function verify( Request $request )
    {
        if ( hash_equals( (string) $request->route( 'id' ), (string) auth()->user()->getKey() ) === false ) {
            throw new AuthorizationException;
        }

        if ( hash_equals( (string) $request->route( 'hash' ), sha1( auth()->user()->getEmailForVerification() ) ) === false ) {
            throw new AuthorizationException;
        }

        if ( auth()->user()->hasVerifiedEmail() ) {
            return $this->sendError( 'Already verified.', [], 202 );
        }

        if ( auth()->user()->markEmailAsVerified() ) {
            event( new Verified( auth()->user() ) );
        }

        return $this->sendResponse( 'Verificado con éxito!', 'Verified successfully.' );
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/email/resend",
     *     operationId="resend",
     *     tags={"Auth"},
     *     summary="Resend the email verification notification",
     *     @OA\Response(
     *         response=200,
     *         description="Verified successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Already verified."
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
    public function resend()
    {
        if ( auth()->user()->hasVerifiedEmail() ) {
            return $this->sendError( 'Already verified.', [], 202 );
        }

        auth()->user()->sendEmailVerificationNotification();

        return $this->sendResponse( 'Email de verification reenviado!', 'Email Verification Notification sended.' );
    }
}
