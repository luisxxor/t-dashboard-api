<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Models\Dashboard\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterAPIController extends AppBaseController
{
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
     *         description="The given data was invalid.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="field-1",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string"
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="field-2",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string"
     *                      )
     *                  )
     *              )
     *          )
     *     )
     * )
     */
    public function register( Request $request )
    {
        $this->validator( $request->all() )->validate();

        event( new Registered( $user = $this->create( $request->all() ) ) );

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
            'name' => ['required', 'string', 'min:2', 'max:30'],
            'lastname' => ['required', 'string', 'min:2', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:30', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:30', 'confirmed'],
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
        $user = User::create( [
            'name' => $data[ 'name' ],
            'lastname' => $data[ 'lastname' ],
            'email' => $data[ 'email' ],
            'password' => $data[ 'password' ],
        ] );

        # probar con assignRole() como en luxury
        // attach default role_id=2 to the new user
        $user->roles()->attach( 2 );

        return $user;
    }
}
