<?php

namespace App\Utils;

class ResponseUtil
{
    /**
     * @param string $message
     * @param mixed  $data
     *
     * @return array
     */
    public static function makeResponse( $message, $data ): array
    {
        return [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];
    }

    /**
     * @param string $message
     * @param array  $errors
     *
     * @return array
     */
    public static function makeError( $message, array $errors = [] ): array
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        if ( empty( $errors ) === false ) {
            $res[ 'errors' ] = $errors;
        }

        return $res;
    }
}
