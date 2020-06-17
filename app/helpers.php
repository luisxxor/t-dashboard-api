<?php

function delete_col( &$array, $offset ) {
    return array_walk( $array, function ( &$v ) use ( $offset ) {
        array_splice( $v, $offset, 1 );
    } );
}

function is_decimal( $value ) {
    if( $value - (int)$value === 0.0 ) {
       return false;
    } else {
       return true;
    }
}