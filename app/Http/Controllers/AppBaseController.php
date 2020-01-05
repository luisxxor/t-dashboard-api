<?php

namespace App\Http\Controllers;

use App\Utils\ResponseUtil;
use Response;

/**
 * This class should be parent class for other API controllers
 * Class AppBaseController
 *
 * @OA\Info(title="Tasing API", version="1.0")
 *
 *  @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Tasing API Server"
 * )
 */
class AppBaseController extends Controller
{
    public function sendResponse( $data, $message, $code = 200 )
    {
        return Response::json( ResponseUtil::makeResponse( $message, $data ), $code );
    }

    public function sendError( $message, $errors = [], $code = 404 )
    {
        return Response::json( ResponseUtil::makeError( $message, $errors ), $code );
    }
}
