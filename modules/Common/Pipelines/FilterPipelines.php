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
    protected $filterSliderFields = [];

    /**
     * @var array
     */
    protected $filterNumericFields = [];

    /**
     * @var array
     */
    protected $filterDropdownFields = [];

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
        $this->initFilterFields();

        $output = [];
        foreach ( $filters as $field => $value ) {
            if ( array_key_exists( $field, $this->filterSliderFields ) === true ) {
                $fieldName = $this->filterSliderFields[ $field ][ 'name' ];

                $fieldValue = $this->getSliderFilter( $field, $value );

                if ( empty( $fieldValue ) !== true ) {
                    $output[ $fieldName ] = $fieldValue;
                }

                continue;
            }

            if ( array_key_exists( $field, $this->filterNumericFields ) === true ) {
                $fieldName = $this->filterNumericFields[ $field ][ 'name' ];

                $fieldValue = $this->getNumericFields( $field, $value );

                if ( empty( $fieldValue ) !== true ) {
                    $output[ $fieldName ] = $fieldValue;
                }

                continue;
            }

            if ( array_key_exists( $field, $this->filterDropdownFields ) === true ) {
                $fieldName = $this->filterDropdownFields[ $field ][ 'name' ];

                $fieldValue = $this->getDropdownFilter( $field, $value );

                if ( empty( $fieldValue ) !== true ) {
                    $output[ $fieldName ] = $fieldValue;
                }

                continue;
            }
        }

        return $output;
    }

    /**
     * Init filter fields with config values.
     *
     * @return void
     */
    protected function initFilterFields(): void
    {
        $this->filterSliderFields = $this->filterSliderFields();
        $this->filterNumericFields = $this->filterNumericFields();
        $this->filterDropdownFields = $this->filterDropdownFields();
    }

    /**
     * Return filter slider fields.
     *
     * @return array
     */
    protected function filterSliderFields(): array
    {
        return [];
    }

    /**
     * Return filter numeric fields.
     *
     * @return array
     */
    protected function filterNumericFields(): array
    {
        return [];
    }

    /**
     * Return filter dropdown fields.
     *
     * @return array
     */
    protected function filterDropdownFields(): array
    {
        return [];
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
        try {
            // get mix and max values
            $arrayValue = explode( '--', $value );
            $minValue = (string)$arrayValue[ 0 ];
            $maxValue = (string)$arrayValue[ 1 ];
        }
        catch ( Exception $e ) {
            return [];
        }

        // helper dictionary
        $dict = $this->filterSliderFields[ $field ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) === true ) {
            $minValue = $dict[ 'clousure' ]( $minValue );
            $maxValue = $dict[ 'clousure' ]( $maxValue );
        }

        // $match aggregation pipeline operators
        if ( $minValue === $maxValue ) {
            return is_float( $maxValue ) === true
                ? [ '$gte' => $maxValue, ]
                : [ '$eq' => $maxValue ];
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
        try {
            // get mix and max values
            $arrayValue = explode( '--', $value );
            $minValue = (string)$arrayValue[ 0 ];
            $maxValue = (string)$arrayValue[ 1 ];
        }
        catch ( Exception $e ) {
            return [];
        }

        // helper dictionary
        $dict = $this->filterNumericFields[ $field ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) === true ) {
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
        $dict = $this->filterDropdownFields[ $field ];

        // callback
        if ( isset( $dict[ 'clousure' ] ) ) {
            $value = $dict[ 'clousure' ]( $value );
        }

        // mask (ILIKE or LIKE)
        if ( isset( $dict[ 'mask' ] ) ) {
            $value = $dict[ 'mask' ]( $value );
        }

        // $match aggregation pipeline operators
        if ( is_array( $value ) === true ) {
            return [ '$in' => $value ];
        }
        return [ '$eq' => $value ];
    }
}
