<?php

namespace App\Http\Controllers\API\Tokens;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Dashboard\PartnerRepository;
use App\Repositories\Tokens\DataTokenRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * @var  PartnerRepository
     */
    private $partnerRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct( DataTokenRepository $dataTokenRepo,
        PartnerRepository $partnerRepo )
    {
        $this->dataTokenRepository = $dataTokenRepo;
        $this->partnerRepository = $partnerRepo;
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
            'data' => [ 'nullable', 'array', 'filled' ],
            'data.partner' => [
                Rule::requiredIf( function () use ( $request ) {
                    return $request->get( 'data' ) !== null;
                } ), 'string', 'filled'
            ],
            'data.project' => [
                Rule::requiredIf( function () use ( $request ) {
                    return $request->get( 'data' ) !== null;
                } ), 'string', 'filled'
            ],
        ] );

        $data = $request->get( 'data' );

        // if there is no given partner-project
        if ( $data === null ) {
            $default = $this->partnerRepository->getDefaultPartnerProject();

            if ( $default === null ) {
                return $this->sendError( 'There is no default partner-project.' );
            }

            $data = [
                'project' => $default->project_code,
                'partner' => $default->partner_code,
            ];
        }

        // validate that partner-project exists
        $partner = $this->partnerRepository->find( $data[ 'partner' ] );
        if ( $partner === null || $partner->hasProject( $data[ 'project' ] ) === false ) {
            return $this->sendError( 'Partner or project not valid.' );
        }

        $dataToken = $this->dataTokenRepository->create( $data );

        return $this->sendResponse( $dataToken->token, 'Data retrieved.' );
    }
}
