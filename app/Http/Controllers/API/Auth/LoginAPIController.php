<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\UserRepository;
use App\Repositories\Tokens\DataTokenRepository;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginAPIController extends AppBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * @OA\Post(
     *     path="/api/login",
     *     operationId="login",
     *     tags={"Auth"},
     *     summary="Handle a login request to the application",
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
     *         name="token",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="User logged successfully.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="user",
     *                      ref="#/components/schemas/User"
     *                  ),
     *                  @OA\Property(
     *                      property="access_token",
     *                      type="string"
     *                  )
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
    use AuthenticatesUsers;

    /**
     * Maximum number of failed attempts to allow.
     *
     * @var int
     */
    protected $maxAttempts = 3;

    /**
     * @var  DataTokenRepository
     */
    private $dataTokenRepository;

    /**
     * @var  UserRepository
     */
    private $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( DataTokenRepository $dataTokenRepo,
        UserRepository $userRepo )
    {
        $this->dataTokenRepository = $dataTokenRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin( Request $request )
    {
        $request->validate( [
            $this->username() => 'required|string|max:30',
            'password' => 'required|string|min:8|max:30',
        ] );
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin( Request $request )
    {
        // validate token
        $request->validate( [ 'token' => [ 'required', 'string', 'exists:data_tokens,token' ] ] );

        return $this->guard()->attempt(
            $this->credentials( $request ), $request->filled( 'remember' )
        );
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse( Request $request )
    {
        // get token
        $dataToken = $this->dataTokenRepository->findAndDelete( $request->get( 'token' ) );

        $this->clearLoginAttempts( $request );

        $user = $this->guard()->user();

        $response = $this->userRepository->login( $user, $dataToken[ 'data' ] );

        return $this->sendResponse( $response, 'User logged successfully.' );
    }
}