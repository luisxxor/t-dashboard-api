<?php

namespace App\Http\Controllers\API\OAuth;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\User as UserResource;
use App\Models\Dashboard\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialiteAPIController extends AppBaseController
{
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/login/{provider}",
     *     operationId="redirect",
     *     tags={"OAuth"},
     *     summary="Retrieve the url to redirect to the given provider authentication page.",
     *     @OA\Parameter(
     *         name="provider",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Provider redirect url retrieve successfully.",
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
     *      )
     * )
     */
    public function redirect( $provider )
    {
        $urlRedirect = Socialite::driver( $provider )->stateless()->redirect()->getTargetUrl();

        return $this->sendResponse( $urlRedirect, 'Provider redirect url retrieve successfully.' );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/login/{provider}/callback",
     *     operationId="callback",
     *     tags={"OAuth"},
     *     summary="Obtain the user information from given provider, retrieve acces token.",
     *     @OA\Parameter(
     *         name="provider",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged successfully.",
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
     *                     property="user",
     *                     ref="#/components/schemas/User"
     *                 ),
     *                 @OA\Property(
     *                     property="access_token",
     *                     type="string"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     * )
     */
    public function callback( $provider )
    {
        // get user info through provider
        $userProvider = Socialite::driver( $provider )->stateless()->user();

        // get the linked social account if exists
        $linkedSocialAccount = \App\Models\Dashboard\LinkedSocialAccount::where( 'provider_name', $provider )
            ->where( 'provider_id', $userProvider->getId() )
            ->first();

        // to store user
        $user = null;

        // if linked account exists
        if ( $linkedSocialAccount !== null ) {
            $user = $linkedSocialAccount->user;
        }
        else {
            // get user email through provider
            $email = $userProvider->getEmail();

            // get user if exists
            $user = User::where( 'email', $email )->first();

            // create user if it is not created yet
            if ( $user === null ) {

                // retrieve user data according to the provider
                switch ( $provider ) {
                    case 'google':
                        $userRaw = $userProvider->getRaw();

                        $userData = [
                            'name' => $userRaw[ 'given_name' ],
                            'lastname' => $userRaw[ 'family_name' ],
                            'email' => $email
                        ];

                        break;

                    case 'facebook':
                        $userRaw = $userProvider->getRaw();

                        $userData = [
                            'name' => $userRaw[ 'first_name' ],
                            'lastname' => $userRaw[ 'last_name' ],
                            'email' => $email
                        ];

                        break;

                    default:
                        $userData = [
                            'name' => $userProvider->getName() ?? '',
                            'lastname' => '',
                            'email' => $email
                        ];
                        break;
                }

                // create user
                $user = User::create( $userData );
                $user->email_verified_at = now();
                $user->save();

                $user->assignRoles( 'regular-user' );
            }

            // create linked social account for user
            $user->linkedSocialAccounts()->create( [
                'provider_id' => $userProvider->getId(),
                'provider_name' => $provider,
            ] );
        }

        // login with id
        $this->guard()->loginUsingId( $user->id );

        // # ver que scopes asignar/crear
        $scopes = [];

        $accessToken = $this->guard()->user()->createToken( 'authToken', $scopes )->accessToken;

        $response = [
            'user' => new UserResource( $this->guard()->user() ),
            'access_token' => $accessToken,
        ];

        return $this->sendResponse( $response, 'User logged successfully.' );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
