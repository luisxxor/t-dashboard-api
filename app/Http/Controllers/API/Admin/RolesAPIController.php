<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Admin\RoleRepository;
use Caffeinated\Shinobi\Models\Role;

/**
 * Class RolesAPIController
 * @package App\Http\Controllers\API\Admin
 */
class RolesAPIController extends AppBaseController
{
    /**
     * @var  RoleRepository
     */
    private $roleRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( RoleRepository $roleRepo )
    {
        $this->roleRepository = $roleRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/admin/roles",
     *     operationId="index",
     *     tags={"Admin"},
     *     summary="Display the list of roles",
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
        $roles = $this->roleRepository->with( 'permissions' )->get();

        return $this->sendResponse( $roles->toArray(), 'Data retrieved.' );
    }
}
