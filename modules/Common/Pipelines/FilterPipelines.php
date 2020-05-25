<?php

namespace Modules\Common\Pipelines;

use Exception;

/**
 * Trait FilterPipelines
 * @package Modules\Common\Pipelines
 * @version May 25, 2020, 05:11 UTC
*/
trait FilterPipelines
{
    /**
     * @var array
     */
    protected $filterFields = [];

    /**
     * Return the content of $geoNear pipeline stage,
     * to calculate distance between the base marker and each property.
     *
     * @param float $lat
     * @param float $lng
     * @param int $maxDistance The maximum distance from the center
     *        point that documents can be (in meters).
     *
     * @return array
     */
    protected function pipelineDistanceToQuery( float $lat, float $lng, int $maxDistance = 1000 ): array
    {
        $geoNear = [
            'near' => [
                'type' => 'Point',
                'coordinates' => [ $lng, $lat ]
            ],
            'spherical' => true,
            'distanceField' => 'distance',
            'maxDistance' => $maxDistance
        ];

        return $geoNear;
    }

    /**
     * Return the content of $match pipeline stage,
     * to filter the result to those within the given polygon.
     *
     * @param  array $arrayShape
     *
     * @return array
     */
    protected function pipelinePropertiesWithinToQuery( array $arrayShape ): array
    {
        $polygon = [];

        $index = 0;
        foreach ( $arrayShape as $value ) {
            $polygon[ $index ][ 0 ] = (float)$value[ 'lng' ];
            $polygon[ $index ][ 1 ] = (float)$value[ 'lat' ];

            $index++;
        }

        // close polygon
        $polygon[] = $polygon[ 0 ];

        // match
        $match = [
            'geo_location' => [
                '$geoWithin' => [
                    '$geometry' => [
                        'type' => 'Polygon' ,
                        'coordinates' => [ $polygon ]
                    ]
                ]
            ]
        ];

        return $match;
    }

    /**
     * Return the content of $match pipeline stage,
     * to filter the result to those that match.
     *
     * @param array $filters
     *
     * @return array
     */
    protected function pipelineFiltersToQuery( array $filters ): array
    {
        $output = [];

        foreach ( $filters as $field => $value ) {
            if ( array_key_exists( $field, $this->getFilterFields()[ 'slidersFields' ] ) === true ) {
                $output[ $this->getFilterFields()[ 'slidersFields' ][ $field ][ 'name' ] ] = $this->getSliderFilter( $field, $value );
                continue;
            }

            if ( array_key_exists( $field, $this->getFilterFields()[ 'numericFields' ] ) === true ) {
                $output[ $this->getFilterFields()[ 'numericFields' ][ $field ][ 'name' ] ] = $this->getNumericFields( $field, $value );
                continue;
            }

            if ( array_key_exists( $field, $this->getFilterFields()[ 'dropdownFields' ] ) === true ) {
                $output[ $this->getFilterFields()[ 'dropdownFields' ][ $field ][ 'name' ] ] = $this->getDropdownFilter( $field, $value );
                continue;
            }
        }

        return $output;
    }

    /**
     * Return filter fields and its settings once.
     *
     * @return array
     */
    protected function getFilterFields(): array
    {
        if ( empty( $this->filterFields ) !== true ) {
            return $this->filterFields;
        }

        $this->filterFields = $this->filterFields();

        return $this->filterFields;
    }

    /**
     * Return filter fields (for $match aggregation pipeline operators).
     *
     * @return array
     */
    protected function filterFields(): array
    {
        throw new Exception( 'should implement filterFields() method.' );
    }

    /**
     * Return $match aggregation pipeline operators
     * for given field and value, slider filter.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return array
     */
    protected function getSliderFilter( string $field, $value ): array
    {
        // helper dictionary
        $dict = $this->filterFields[ 'slidersFields' ][ $field ];

        // get mix and max values
        $arrayValue = explode( '--', $value );
        $minValue = (string)$arrayValue[ 0 ];
        $maxValue = (string)$arrayValue[ 1 ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) === true ) {
            $minValue = $dict[ 'clousure' ]( $minValue );
            $maxValue = $dict[ 'clousure' ]( $maxValue );
        }

        // $match aggregation pipeline operators
        if ( $minValue === $maxValue ) {
            return [ '$eq' => $minValue ];
        }
        if ( is_float( $maxValue ) === true ) {
            return [ '$gte' => $minValue, ];
        }
        return [ '$gte' => $minValue, '$lte' => $maxValue ];
    }

    /**
     * Return $match aggregation pipeline operators
     * for given field and value, numeric filter.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return array
     */
    protected function getNumericFields( string $field, $value ): array
    {
        // helper dictionary
        $dict = $this->filterFields[ 'numericFields' ][ $field ];

        // get mix and max values
        $arrayValue = explode( '--', $value );
        $minValue = (string)$arrayValue[ 0 ];
        $maxValue = (string)$arrayValue[ 1 ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) ) {
            $minValue = $dict[ 'clousure' ]( $minValue );
            $maxValue = $dict[ 'clousure' ]( $maxValue );
        }

        // $match aggregation pipeline operators
        if ( $minValue === $maxValue ) {
            return [ '$eq' => $minValue ];
        }
        return [ '$gte' => $minValue, '$lte' => $maxValue ];
    }

    /**
     * Return $match aggregation pipeline operators
     * for given field and value, dropdown filter.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return array
     */
    protected function getDropdownFilter( string $field, $value ): array
    {
        // helper dictionary
        $dict = $this->filterFields[ 'dropdownFields' ][ $field ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) ) {
            $finalValue = $dict[ 'clousure' ]( $finalValue );
        }

        // mask (ILIKE or LIKE)
        if ( isset( $dict[ 'mask' ] ) ) {
            $finalValue = $dict[ 'mask' ]( $finalValue );
        }

        // $match aggregation pipeline operators
        if ( is_array( $finalValue ) === true ) {
            return [ '$in' => $finalValue ];
        }
        return [ '$eq' => $finalValue ];
    }
}
