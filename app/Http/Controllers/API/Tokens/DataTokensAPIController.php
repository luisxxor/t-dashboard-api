<?php

namespace App\Http\Controllers\API\Tokens;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Tokens\DataTokenRepository;
use Caffeinated\Shinobi\Models\Role;
use Illuminate\Http\Request;

/**
 * Class DataTokensAPIController
 * @package App\Http\Controllers\API\Tokens
 */
class DataTokensAPIController extends AppBaseController
{
    /**
     * @var  DataTokenRepository
     */
    private $dataTokenRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( DataTokenRepository $dataTokenRepo )
    {
        $this->dataTokenRepository = $dataTokenRepo;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/tokens/auth_token",
     *     operationId="create",
     *     tags={"Tokens"},
     *     summary="Generate auth token",
     *     @OA\Parameter(
     *         name="data",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items()
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data retrieved.",
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
     *     )
     * )
     */
    public function create( Request $request )
    {
        $request->validate( [
            'data' => [ 'required', 'array', 'filled' ],
        ] );

        $data = $request->get( 'data' );

        $dataToken = $this->dataTokenRepository->create( $data );

        return $this->sendResponse( $dataToken->token, 'Data retrieved.' );
    }
}
