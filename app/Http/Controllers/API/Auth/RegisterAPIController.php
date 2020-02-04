<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\User as UserResource;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterAPIController extends AppBaseController
{
    /**
     * @var  UserRepository
     */
    private $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( UserRepository $userRepo )
    {
        $this->userRepository = $userRepo;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/register",
     *     operationId="register",
     *     tags={"Auth"},
     *     summary="Handle a registration request for the application",
     *     @OA\Parameter(
     *         name="name",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lastname",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *      @OA\Response(
     *          response=200,
     *          description="User registered successfully.",
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
    public function register( Request $request )
    {
        $input = $request->only( [ 'name', 'lastname', 'email', 'password', 'password_confirmation', 'token' ] );

        $this->validator( $input )->validate();

        event( new Registered( $user = $this->create( $input ) ) );

        // # ver que scopes asignar/crear
        $scopes = [];

        $accessToken = $user->createToken( 'authToken', $scopes )->accessToken;

        $response = [
            'user' => new UserResource( $user ),
            'access_token' => $accessToken,
        ];

        return $this->sendResponse( $response, 'User registered successfully.' );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator( array $data )
    {
        return Validator::make( $data, [
            'name'      => [ 'required', 'string', 'min:2', 'max:30' ],
            'lastname'  => [ 'required', 'string', 'min:2', 'max:30' ],
            'email'     => [ 'required', 'string', 'email', 'max:30', 'unique:users' ],
            'password'  => [ 'required', 'string', 'min:8', 'max:30', 'confirmed' ],
            'token'     => [ 'required', 'string', Rule::in( array_keys( config( 'multi-api' ) ) ) ],
        ] );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\Dashboard\User
     */
    protected function create( array $data )
    {
        $user = $this->userRepository->create( [
            'name' => $data[ 'name' ],
            'lastname' => $data[ 'lastname' ],
            'email' => $data[ 'email' ],
            'password' => $data[ 'password' ],
            'accessible_projects' => [ $data[ 'token' ] ],
        ] );

        $user->assignRoles( 'regular-user' );

        return $user;
    }
}
