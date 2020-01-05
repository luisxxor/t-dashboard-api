<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordAPIController extends AppBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     operationId="reset",
     *     tags={"Auth"},
     *     summary="Reset the given user's password",
     *     @OA\Parameter(
     *         name="email",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Password reseted.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=422,
     *         description="The given data was invalid."
     *     )
     * )
     */
    use ResetsPasswords;

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword( $user, string $password )
    {
        $user->password = $password;

        $user->setRememberToken( Str::random( 60 ) );

        $user->save();

        event( new PasswordReset( $user ) );

        $this->guard()->login( $user );
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse( Request $request, $response )
    {
        return $this->sendResponse( trans( $response ), 'Password reseted.' );
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse( Request $request, $response )
    {
        $errors = [];

        if ( $response === Password::INVALID_USER ) {
            $errors[ 'email' ] = [ trans( $response ) ];
        }

        if ( $response === Password::INVALID_TOKEN ) {
            $errors[ 'token' ] = [ trans( $response ) ];
        }

        return $this->sendError( 'The given data was invalid.', $errors, 422 );
    }
}