<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Dashboard\ProjectRepository;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class UsersAPIController
 * @package App\Http\Controllers\API\Admin
 */
class UsersAPIController extends AppBaseController
{
    /**
     * @var  UserRepository
     */
    private $userRepository;

    /**
     * @var  ProjectRepository
     */
    private $projectRepository;

    /**
     * @var  RoleRepository
     */
    private $roleRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( UserRepository $userRepo,
        ProjectRepository $projectRepo,
        RoleRepository $roleRepo )
    {
        $this->userRepository = $userRepo;
        $this->projectRepository = $projectRepo;
        $this->roleRepository = $roleRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/admin/users",
     *     operationId="index-",
     *     tags={"Admin"},
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
     *                 type="object"
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

        return $this->sendResponse( $users, 'Users data retrived.' );
    }

    /**
     * @param  int $userId
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/admin/admin/users/{userId}",
     *     operationId="update",
     *     tags={"Admin"},
     *     summary="Update the specified user's with given data",
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
     *         required=false,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="roles",
     *         required=false,
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
     *                 type="object"
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
        $input = $request->only( [
            'name', 'lastname', 'phone_number1', 'address_line1', 'address_line2', 'password',

            'accessible_projects',

            'roles',
        ] );

        $projects = array_column( $this->projectRepository->all( [], null, null, [ 'code' ] )->toArray(), 'code' );

        $roles = array_column( $this->roleRepository->all( [], null, null, [ 'slug' ] )->toArray(), 'slug' );

        Validator::make( $input, [
            'name' => [ 'required', 'string', 'min:2', 'max:30' ],
            'lastname' => [ 'required', 'string', 'min:2', 'max:30' ],
            'phone_number1' => [ 'nullable', 'string' ],
            'address_line1' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'address_line2' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'password' => [ 'nullable', 'string', 'min:8', 'max:30' ],

            'accessible_projects' => [ 'nullable', 'array', 'filled', Rule::in( $projects ) ],

            'roles' => [ 'nullable', 'array', 'filled', Rule::in( $roles ) ],
        ] )->validate();

        $user = $this->userRepository->find( $userId );

        if ( empty( $user ) === true ) {
            return $this->sendError( 'User not found.', [], 404 );
        }

        $user = $this->userRepository->update( $input, $userId );

        if ( $request->get( 'roles' ) !== null ) {
            $user->syncRoles( $request->get( 'roles' ) );
        }

        return $this->sendResponse( $user->append( 'role_list' )->toArray(), 'User updated successfully.' );
    }
}
