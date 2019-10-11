<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Dashboard\UpdateProfileAPIRequest;
use App\Http\Resources\User as UserResource;
use App\Repositories\Dashboard\ProfileRepository;
use App\Rules\CurrentPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;

/**
 * Class ProfileAPIController
 * @package App\Http\Controllers\API\Dashboard
 */
class ProfileAPIController extends AppBaseController
{
    /** @var  ProfileRepository */
    private $profileRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( ProfileRepository $profileRepo )
    {
        $this->profileRepository = $profileRepo;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/dashboard/profile",
     *     operationId="show",
     *     tags={"Profile"},
     *     summary="Display the authenticated user's Profile",
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully.",
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
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function show( Request $request )
    {
        $user = new UserResource( auth()->user() );

        return $this->sendResponse( $user, 'Profile retrieved successfully.' );
    }

    /**
     * @param UpdateProfileAPIRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/dashboard/profile",
     *     operationId="update",
     *     tags={"Profile"},
     *     summary="Update the authenticated user's Profile with given data",
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
     *         name="old_password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully.",
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
     *     ),
     *     security={
     *         {"": {}}
     *     }
     * )
     */
    public function update( Request $request )
    {
        $input = $request->only( [ 'name', 'lastname', 'phone_number1', 'address_line1', 'address_line2', 'old_password' ] );

        Validator::make( $input, [
            'name' => [ 'required', 'string', 'min:2', 'max:30' ],
            'lastname' => [ 'required', 'string', 'min:2', 'max:30' ],
            'phone_number1' => [ 'nullable', 'string' ],
            'address_line1' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'address_line2' => [ 'nullable', 'string', 'min:5', 'max:50' ],
            'old_password' => [ 'bail', 'required', 'string', new CurrentPassword ],
        ] )->validate();

        $user = auth()->user();

        $profile = $this->profileRepository->update( $input, $user->id );

        $user = new UserResource( $profile );

        return $this->sendResponse( $user, 'Profile updated successfully.' );
    }
}
