<?php

namespace App\Http\Controllers\API\OAuth;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\User as UserResource;
use App\Providers\GoogleProvider;
use App\Repositories\Dashboard\UserRepository;
use App\Repositories\Tokens\DataTokenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Socialite;

class SocialiteAPIController extends AppBaseController
{
    /**
     * @var  UserRepository
     */
    private $userRepository;

    /**
     * @var  DataTokenRepository
     */
    private $dataTokenRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( UserRepository $userRepo,
        DataTokenRepository $dataTokenRepo )
    {
        $this->userRepository = $userRepo;
        $this->dataTokenRepository = $dataTokenRepo;

        Socialite::extend( 'google', function ( $container ) {
            $config = $container[ 'config' ][ 'services.google' ];
            $redirect = value( $config[ 'redirect' ] );
            return new GoogleProvider(
                $container[ 'request' ],
                $config[ 'client_id' ],
                $config[ 'client_secret' ],
                Str::startsWith( $redirect, '/' ) ? $container[ 'url' ]->to( $redirect ) : $redirect,
                Arr::get( $config, 'guzzle', [] )
            );
        } );
    }

    /**
     * @param  string $provider
     * @param  \Illuminate\Http\Request $request
     *
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
    public function redirect( $provider, Request $request )
    {
        // validate and get token
        $request->validate( [ 'token' => [ 'required', 'string', 'exists:data_tokens,token' ] ] );
        $token = $request->get( 'token' );

        $urlRedirect = Socialite::driver( $provider )->setState( $token )->redirect()->getTargetUrl();

        return $this->sendResponse( $urlRedirect, 'Provider redirect url retrieve successfully.' );
    }

    /**
     * @param  string $provider
     * @param  \Illuminate\Http\Request $request
     *
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
     *     @OA\Parameter(
     *         name="token",
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
    public function callback( $provider, Request $request )
    {
        // validate and get token
        $request->validate( [ 'state' => [ 'required', 'string', 'exists:data_tokens,token' ] ] );
        $dataToken = $this->dataTokenRepository->findAndDelete( $request->get( 'state' ) );

        // get user info through provider
        $userProvider = Socialite::driver( $provider )->stateless()->user();

        dd( $userProvider );

        // get the linked social account if exists
        $linkedSocialAccount = \App\Models\Dashboard\LinkedSocialAccount::where( 'provider_name', $provider )
            ->where( 'provider_id', $userProvider->getId() )
            ->first();

        // if linked account exists
        if ( empty( $linkedSocialAccount ) === false ) {
            $user = $linkedSocialAccount->user;
        }
        else {
            // get user if exists
            $user = $this->userRepository->findByField( 'email', $userProvider->getEmail() )->first();

            // create user if it is not created yet
            if ( empty( $user ) === true ) {
                $user = $this->registerUser( $userProvider, $provider, $dataToken );
            }

            // create linked social account for user
            $user->linkedSocialAccounts()->create( [
                'provider_id' => $userProvider->getId(),
                'provider_name' => $provider,
            ] );
        }

        // login with id
        # $this->guard()->loginUsingId( $user->id );

        // scopes to which the user has access
        $scopes = $user->getScopes();

        $accessToken = $user->createToken( 'authToken', $scopes )->accessToken;

        $response = [
            'user' => new UserResource( $user ),
            'access_token' => $accessToken,
        ];

        return $this->sendResponse( $response, 'User logged successfully.' );
    }

    /**
     * Registers an user with provider data.
     *
     * @param \Laravel\Socialite\Two\User $userProviderData
     * @param string $provider
     * @param \App\Models\Tokens\DataToken $dataToken
     *
     * @return \App\Models\Dashboard\User
     */
    protected function registerUser( $userProviderData, string $provider, $dataToken )
    {
        $userData = [
            'email' => $userProviderData->getEmail()
        ];

        // retrieve user data according to the provider
        switch ( $provider ) {
            case 'google':
                $userRaw = $userProviderData->getRaw();

                $userData[ 'name' ] = $userRaw[ 'given_name' ];
                $userData[ 'lastname' ] = $userRaw[ 'family_name' ];

                break;

            case 'facebook':
                $userRaw = $userProviderData->getRaw();

                $userData[ 'name' ] = $userRaw[ 'first_name' ];
                $userData[ 'lastname' ] = $userRaw[ 'last_name' ];

                break;

            default:
                $userData[ 'name' ] = $userProviderData->getName() ?? '';
                $userData[ 'lastname' ] = '';

                break;
        }

        // array of accessible prtner-projects for user
        $userData[ 'accessible_projects' ] = [ $dataToken[ 'data' ] ];

        $user = $this->create( $userData );

        return $user;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     *
     * @return \App\Models\Dashboard\User
     */
    protected function create( array $data )
    {
        $user = $this->userRepository->create( [
            'name' => $data[ 'name' ],
            'lastname' => $data[ 'lastname' ],
            'email' => $data[ 'email' ],
            'email_verified_at' => now(),
            'accessible_projects' => $data[ 'accessible_projects' ],
        ] );

        $user->assignRoles( 'regular-user' );

        return $user;
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
