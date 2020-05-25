<?php

namespace Modules\Common\Repositories;

/**
 * Class CommonRepository
 * @package Modules\Common\Repositories
 * @version May 24, 2020, 18:48 UTC
*/
abstract class CommonRepository
{
    /**
     * Flattened header for export files
     * (will be fill it when flattenedHeader() method is called).
     *
     * @var array
     */
    protected $flattenedHeader = [];

    /**
     * Header for export files (with nested values, if any).
     *
     * @var array
     */
    protected $header;

    public function flattenedHeader()
    {
        if ( empty( $this->flattenedHeader ) !== true ) {
            return $this->flattenedHeader;
        }

        $counter = 0;
        $this->flattenedHeader = $this->header;
        foreach ( $this->flattenedHeader as $key => $value ) {
            if ( is_array( $value ) === true ) {
                $this->flattenedHeader = array_slice( $this->flattenedHeader, 0, $counter ) + $value + array_slice( $this->flattenedHeader, $counter + 1 );
            }

            $counter++;
        }

        return $this->flattenedHeader;
    }
}
