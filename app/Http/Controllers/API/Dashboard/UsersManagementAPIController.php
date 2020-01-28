<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\User as UserResource;
use App\Repositories\Dashboard\UserRepository;
use Illuminate\Http\Request;
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

        $resource = UserResource::collection( $users );

        return $this->sendResponse( $resource, 'Users data retrived.' );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/users/{id}/orders",
     *     operationId="ordersByUser",
     *     tags={"Users Management"},
     *     summary="Display the orders of an user",
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
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function ordersByUser( $id, Request $request )
    {
        $user = $this->userRepository->find( $id );

        if ( empty( $user ) === true ) {
            \Log::info( 'User not found.', [ $id ] );

            return $this->sendError( 'User not found.', [], 404 );
        }

        $orders = $user->orders()->get()->sortByDesc( 'created_at' );

        return $this->sendResponse( array_values( $orders->toArray() ), 'User\'s orders data retrieved.' );
    }
}
