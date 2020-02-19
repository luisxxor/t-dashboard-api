<?php

namespace App\Projects\PeruVehicles\Pipelines;

use App\Projects\PeruVehicles\Models\PublicationType;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

/**
 * Trait FilterPipelines
 * @package App\Projects\PeruVehicles\Pipelines
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
    protected function pipelineFiltersToQuery( $filters , $publication_type): array
    {   
        switch ($publication_type) {
            case $this->constants[ 'CAR' ]:
                $filterFields = [
                    'slidersFields' => [
                        $this->constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ],
                            'clousure' => function ( $field ) {
                                return ( $field === '5' ) ? 5.1 : (float)$field;
                            }
                        ]
                    ],
                    'numericFields' => [
                        $this->constants[ 'FILTER_FIELD_MILEAGE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MILEAGE' ],
                            'clousure' => function ( $field ) {
                                return (int)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_YEAR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_YEAR' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ]
                    ],
                    'combosFields' => [
                        $this->constants[ 'FILTER_FIELD_MAKE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAKE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_MODEL' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MODEL' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CATEGORY' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CATEGORY' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_TRANSMISSION' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_TRANSMISSION' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_STEERING_WHEEL' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_STEERING_WHEEL' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_COLOR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_COLOR' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                        ]
                    ]
                ];
                break;
            
            case $this->constants[ 'MOTORCYCLES' ]:
                $filterFields = [
                    'numericFields' => [
                        $this->constants[ 'FILTER_FIELD_MILEAGE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MILEAGE' ],
                            'clousure' => function ( $field ) {
                                return (int)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_YEAR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_YEAR' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ]
                    ],
                    'combosFields' => [
                        $this->constants[ 'FILTER_FIELD_MAKE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAKE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_MODEL' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MODEL' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CATEGORY' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CATEGORY' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_TRANSMISSION' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_TRANSMISSION' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_BRAKES' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_BRAKES' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_STARTER_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_STARTER_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_COLOR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_COLOR' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                        ]
                    ]
                ];
                break;

            case $this->constants[ 'MOTORCYCLES' ]:
                $filterFields = [
                    'slidersFields' => [
                        $this->constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ],
                            'clousure' => function ( $field ) {
                                return ( $field === '5' ) ? 5.1 : (float)$field;
                            }
                        ]
                    ],
                    'numericFields' => [
                        $this->constants[ 'FILTER_FIELD_MILEAGE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MILEAGE' ],
                            'clousure' => function ( $field ) {
                                return (int)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_YEAR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_YEAR' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_MAX_POWER' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAX_POWER' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_TIRE_SIZE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_TIRE_SIZE' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_MAX_CARGO' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAX_CARGO' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ],
                        $this->constants[ 'FILTER_FIELD_MAX_PASSENGERS' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAX_PASSENGERS' ],
                            'clousure' => function ( $field ) {
                                return (float)$field;
                            }
                        ]
                    ],
                    'combosFields' => [
                        $this->constants[ 'FILTER_FIELD_MAKE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MAKE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_MODEL' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_MODEL' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CATEGORY' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CATEGORY' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_TRANSMISSION' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_TRANSMISSION' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_BRAKES' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_BRAKES' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_STEERING_WHEEL' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_STEERING_WHEEL' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_COLOR' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_COLOR' ],
                        ],
                        $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ] => [
                            'name' => $this->constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                        ]
                    ]
                ];
                break;
            default:
                break;
        }

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
}
