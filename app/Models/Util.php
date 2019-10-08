<?php

namespace App\Models;

class Util
{
    public static function explodeInterval( $intervalString, $returnFloat = false )
    {
        $array = explode( '--', $intervalString );

        if ( $returnFloat === false ) {
            $first_value  = (int)$array[ 0 ];
            $second_value = (int)$array[ 1 ];
        }
        else {
            $first_value  = (float)$array[ 0 ];
            $second_value = (float)$array[ 1 ];
        }

        $array_return[ 'min' ] = $first_value;
        $array_return[ 'max' ] = $second_value;

        return $array_return;
    }
}
