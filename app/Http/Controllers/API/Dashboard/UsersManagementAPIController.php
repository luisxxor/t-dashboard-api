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
}
