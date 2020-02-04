<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
# TODO crear recurso usuario para management
// use App\Http\Resources\User as UserResource;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Response;

/**
 * Class UsersManagementAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class UsersManagementAPIController extends AppBaseController
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
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/users",
     *     operationId="index",
     *     tags={"Users Management"},
     *     summary="Display the list of users",
     *     @OA\Response(
     *         response=200,
     *         description="Data retrived.",
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
     *                     @OA\Items(
     *                         ref="#/components/schemas/User"
     *                     )
     *                 ),
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
    public function index()
    {
        $users = $this->userRepository->all();

        // $resource = UserResource::collection( $users );

        return $this->sendResponse( $users, 'Users data retrived.' );
    }

    /**
     * @param  int $userId
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/dashboard/admin/users/{userId}",
     *     operationId="update",
     *     tags={"Users Management"},
     *     summary="Update the specified user's User with given data",
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
     *         name="phone_number1",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address_line1",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address_line2",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="accessible_projects",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
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
     *     @OA\Response(
     *         response=404,
     *         description="Data not found."
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
    public function update( $userId, Request $request )
    {
        $input = $request->only( [ 'name', 'lastname', 'phone_number1', 'address_line1', 'address_line2', 'password', 'accessible_projects' ] );

        Validator::make( $input, [
            'name' => [ 'required', 'string', 'min:2', 'max:30' ],
            'lastname' => [ 'required', 'string', 'min:2', 'max:30' ],
            'phone_number1' => [ 'nullable', 'string' ],
            'address_line1' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'address_line2' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'password' => [ 'nullable', 'string', 'min:8', 'max:30' ],
            'accessible_projects' => [ 'required', 'array', 'filled', Rule::in( array_keys( config( 'multi-api' ) ) ) ],
        ] )->validate();

        $user = $this->userRepository->find( $userId );

        if ( empty( $user ) === true ) {
            return $this->sendError( 'User not found.', [], 404 );
        }

        $user = $this->userRepository->update( $input, $userId );

        // $user = new UserResource( $user );

        return $this->sendResponse( $user, 'User updated successfully.' );
    }
}
