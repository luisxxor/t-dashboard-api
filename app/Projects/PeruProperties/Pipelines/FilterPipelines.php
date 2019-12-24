<?php

namespace App\Projects\PeruProperties\Pipelines;

use App\Projects\PeruProperties\Models\PropertyType;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

/**
 * Trait FilterPipelines
 * @package App\Projects\PeruProperties\Pipelines
 * @version Dec 24, 2019, 16:37 UTC
*/
trait FilterPipelines
{
    /**
     * Return the filters to the query.
     *
     * @param array $filters
     *
     * @return array
     */
    protected function pipelineFiltersToQuery( $filters ): array
    {
        $filterFields = [
            'slidersFields' => [
                'bedrooms' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BEDROOMS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ],
                'bathrooms' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BATHROOMS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ],
                'parkings' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PARKINGS' ],
                    'clousure' => function ( $field ) {
                        return ( $field === '5' ) ? 5.1 : (float)$field;
                    }
                ]
            ],
            'numericFields' => [
                'antiquity_years' => [
                    'name' => $this->constants[ 'FILTER_FIELD_ANTIQUITY_YEARS' ],
                    'clousure' => function ( $field ) {
                        return (int)$field;
                    }
                ],
                'total_area_m2' => [
                    'name' => $this->constants[ 'FILTER_FIELD_TOTAL_AREA_M2' ],
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                'build_area_m2' => [
                    'name' => $this->constants[ 'FILTER_FIELD_BUILD_AREA_M2' ],
                    'clousure' => function ( $field ) {
                        return (float)$field;
                    }
                ],
                'publication_date' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PUBLICATION_DATE' ],
                    'clousure' => function ( $field ) {
                        $carbonDate = Carbon::createFromFormat( 'd/m/Y', trim( $field ) );

                        # evaluar si es necesario convertir a UTCDateTime
                        return new UTCDateTime( $carbonDate );
                    },
                ]
            ],
            'combosFields' => [
                'property_type_id' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PROPERTY_TYPE' ],
                    'clousure' => function ( $field ) {
                        // select
                        $results = PropertyType::where( 'owner_name', $field )->get();

                        return array_column( $results->toArray(), '_id' );
                    },
                ],
                'publication_type' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                ],
                'property_new' => [
                    'name' => $this->constants[ 'FILTER_FIELD_PROPERTY_NEW' ],
                    'clousure' => function ( $field ) {
                        return (bool)$field;
                    },
                ]
            ]
        ];

        $output = [];

        // para slidersFields
        foreach ( $filterFields[ 'slidersFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name' ] ] ) ) {

                // si no viene vacio el campo
                if ( empty( $filters[ $field[ 'name' ] ] ) === false ) { // si el campo viene vacio viene un string vacio

                    // obtenemos ambos valores del rango
                    $field_array = explode( '--', $filters[ $field[ 'name' ] ] );
                    $min_field = (string)(int)$field_array[ 0 ];
                    $max_field = (string)(int)$field_array[ 1 ];

                    //ejecutamos un callback, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $min_field = $field[ 'clousure' ]( $min_field );
                        $max_field = $field[ 'clousure' ]( $max_field );
                    }

                    //se realiza where
                    if ( $min_field === $max_field ) {
                        $output[ $key ] = [ '$eq' => $min_field ];
                    }
                    else {
                        if ( is_decimal( $max_field ) === true ) {
                            $output[ $key ] = [ '$gte' => $min_field, ];
                        }
                        else {
                            $output[ $key ] = [ '$gte' => $min_field, '$lte' => $max_field ];
                        }
                    }
                }
            }
        }

        // para numericFields
        foreach ( $filterFields[ 'numericFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name'] ] ) ) {

                // si no viene vacio el campo
                if ( $filters[ $field[ 'name' ] ] !== '--' ) { // si el campo viene vacio viene solo '=='

                    // obtenemos ambos valores del rango
                    $field_array = explode( '--', $filters[ $field[ 'name' ] ] );
                    $min_field = (string)$field_array[ 0 ];
                    $max_field = (string)$field_array[ 1 ];

                    //ejecutamos un callback, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $min_field = $field[ 'clousure' ]( $min_field );
                        $max_field = $field[ 'clousure' ]( $max_field );
                    }

                    //se realiza where
                    if ( $min_field === $max_field ) {
                        $output[ $key ] = [ '$eq' => $min_field ];
                    }
                    else {
                        $output[ $key ] = [ '$gte' => $min_field, '$lte' => $max_field ];
                    }
                }
            }
        }

        // para combosFields
        foreach ( $filterFields[ 'combosFields' ] as $key => $field ) {

            // si viene el campo
            if ( isset( $filters[ $field[ 'name' ] ] ) ) {

                // si no viene vacio el campo
                if ( $filters[ $field[ 'name' ] ] !== null || $filters[ $field[ 'name' ] ] !== '' ) {

                    //obtenemos el campo del filters que viene del buscador
                    $finalField = $filters[ $field[ 'name' ] ];

                    //ejecutamos un callback para la busqueda, en caso de ser necesario
                    if ( isset( $field[ 'clousure' ] ) ) {
                        $finalField = $field[ 'clousure' ]( $finalField );
                    }

                    //ejecutamos un callback para la mascara (para operadores ILIKE o LIKE), en caso de ser necesario
                    if ( isset( $field[ 'mask' ] ) ) {
                        $finalField = $field[ 'mask' ]( $finalField );
                    }

                    //se realiza where considerando si incluye operador espedifico
                    if ( is_array( $finalField ) === true ) {
                        $output[ $key ] = [ '$in' => $finalField ];
                    }
                    else {
                        $output[ $key ] = [ '$eq' => $finalField ];
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Return the array of vertices of the polygon to the query.
     *
     * @param  array $arrayShape
     *
     * @return array
     */
    protected function pipelinePropertiesWithinToQuery( $arrayShape ): array
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
     * Get the distance between the base marker and each property.
     *
     * @param float $lat
     * @param float $lng
     *
     * @return array
     */
    protected function pipelineDistanceToQuery( float $lat, float $lng ): array
    {
        $geoNear = [
            'near' => [
                'type' => 'Point',
                'coordinates' => [ $lng, $lat ]
            ],
            'spherical' => true,
            'distanceField' => 'distance',
            '$limit' => 100000 # pendiente definir este limite
        ];

        return $geoNear;
    }
}
