<?php

$constants = config( 'multi-api.pe-vehicles.constants' );

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Vehicles Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'searchURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/peru_vehicles/search',
    ],

    'paginationURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/peru_vehicles/paginate',
    ],

    'processOrderURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/peru_vehicles/order',
    ],

    'paymentTypes' => [
        [
            'paymentType' => config( 'constants.payment_gateways.MERCADOPAGO' ),
            'currency' => config( 'constants.payment_currencies.PEN' )
        ],
    ],

    'mapMarkers' => [
        'visible' => false
    ],

    'containerItems' => [
        'visible' => true,
        'perpage' => 10,
    ],

    'filters' => [
        $constants[ 'GENERAL' ] => [
            'fields' => [
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                    'label' => 'Tipo de Busqueda',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de busqueda',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/publication_type',
                ]
            ],
        ],
        $constants[ 'CAR' ] => [
            'fields' => [
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAKE' ],
                    'label' => 'Marca del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de marca',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MAKE' ].'/'.$constants[ 'CAR' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MODEL' ],
                    'label' => 'Modelo del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de modelo',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MODEL' ].'/'.$constants[ 'CAR' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CATEGORY' ],
                    'label' => 'Categoria del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de categoria',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_CATEGORY' ].'/'.$constants[ 'CAR' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_TRANSMISSION' ],
                    'label' => 'Transmision del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmision',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_TRANSMISSION' ].'/'.$constants[ 'CAR' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                    'label' => 'Tipo de combustible del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de combustible',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_FUEL_TYPE' ].'/'.$constants[ 'CAR' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_STEERING_WHEEL' ],
                    'label' => 'Tipo de volante del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de volante',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_STEERING_WHEEL' ].'/'.$constants[ 'CAR' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                    'label' => 'Tipo de transmisión del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmisión',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_DRIVE_TYPE' ].'/'.$constants[ 'CAR' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_COLOR' ],
                    'label' => 'Color del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el color',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_COLOR' ].'/'.$constants[ 'CAR' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ],
                    'label' => 'Numero de Puertas',
                    'type' => 'slider',
                    'values' => [
                        [
                            'value' => 0,
                            'label' => '0',
                        ],
                        [
                            'value' => 1,
                            'label' => '1',
                        ],
                        [
                            'value' => 2,
                            'label' => '2',
                        ],
                        [
                            'value' => 3,
                            'label' => '3',
                        ],
                        [
                            'value' => 4,
                            'label' => '4',
                        ],
                        [
                            'value' => 5.1,
                            'label' => '5 a más',
                        ],
                    ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MILEAGE' ],
                    'label' => 'Kilometraje Recorrido',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_YEAR' ],
                    'label' => 'Año del Vehiculo',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                    'label' => 'Desplazamiento del motor',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                    'label' => 'Número de cilindros',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                    'label' => 'Tipo de Condición',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de publicación',
                    'values' =>
                    [
                        [
                            'value' => 'venta',
                            'text' => 'Venta',
                        ]
                    ],
                ],
            ],
        ],
        $constants[ 'BUSESTRUCK' ] => [
            'fields' => [
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAKE' ],
                    'label' => 'Marca del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de marca',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MAKE' ].'/'.$constants[ 'BUSESTRUCK' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MODEL' ],
                    'label' => 'Modelo del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de modelo',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MODEL' ].'/'.$constants[ 'BUSESTRUCK' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CATEGORY' ],
                    'label' => 'Categoria del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de categoria',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_CATEGORY' ].'/'.$constants[ 'BUSESTRUCK' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_TRANSMISSION' ],
                    'label' => 'Transmision del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmision',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_TRANSMISSION' ].'/'.$constants[ 'BUSESTRUCK' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                    'label' => 'Tipo de combustible del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de combustible',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_FUEL_TYPE' ].'/'.$constants[ 'BUSESTRUCK' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_BRAKES' ],
                    'label' => 'Tipo de frenos del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de frenos',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_BRAKES' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_STEERING_WHEEL' ],
                    'label' => 'Tipo de volante del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de volante',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_STEERING_WHEEL' ].'/'.$constants[ 'BUSESTRUCK' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                    'label' => 'Tipo de transmisión del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmisión',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_DRIVE_TYPE' ].'/'.$constants[ 'BUSESTRUCK' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_COLOR' ],
                    'label' => 'Color del Vehiculo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el color',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_COLOR' ].'/'.$constants[ 'BUSESTRUCK' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_NUMBER_OF_DOORS' ],
                    'label' => 'Numero de Puertas',
                    'type' => 'slider',
                    'values' => [
                        [
                            'value' => 0,
                            'label' => '0',
                        ],
                        [
                            'value' => 1,
                            'label' => '1',
                        ],
                        [
                            'value' => 2,
                            'label' => '2',
                        ],
                        [
                            'value' => 3,
                            'label' => '3',
                        ],
                        [
                            'value' => 4,
                            'label' => '4',
                        ],
                        [
                            'value' => 5.1,
                            'label' => '5 a más',
                        ],
                    ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MILEAGE' ],
                    'label' => 'Kilometraje Recorrido',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_YEAR' ],
                    'label' => 'Año del Vehiculo',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                    'label' => 'Desplazamiento del motor',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                    'label' => 'Número de cilindros',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAX_POWER' ],
                    'label' => 'Maximo de poder',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_TIRE_SIZE' ],
                    'label' => 'Tamaño de llanta',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAX_CARGO' ],
                    'label' => 'Maximo de carga',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAX_PASSENGERS' ],
                    'label' => 'Maximo de pasajeros',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                    'label' => 'Tipo de Condición',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de publicación',
                    'values' =>
                    [
                        [
                            'value' => 'venta',
                            'text' => 'Venta',
                        ]
                    ],
                ],
            ],
        ],
        $constants[ 'MOTORCYCLES' ] => [
            'fields' => [
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MAKE' ],
                    'label' => 'Marca de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de marca',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MAKE' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MODEL' ],
                    'label' => 'Modelo de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de modelo',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_MODEL' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CATEGORY' ],
                    'label' => 'Categoria de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de categoria',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_CATEGORY' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_TRANSMISSION' ],
                    'label' => 'Transmision de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmision',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_TRANSMISSION' ].'/'.$constants[ 'MOTORCYCLES' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_FUEL_TYPE' ],
                    'label' => 'Tipo de combustible de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de combustible',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_FUEL_TYPE' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_BRAKES' ],
                    'label' => 'Tipo de frenos de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de frenos',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_BRAKES' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_STARTER_TYPE' ],
                    'label' => 'Tipo de encendido de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de encendido',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_STARTER_TYPE' ].'/'.$constants[ 'MOTORCYCLES' ],
                ], 
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_DRIVE_TYPE' ],
                    'label' => 'Tipo de transmisión de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de transmisión',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_DRIVE_TYPE' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_COLOR' ],
                    'label' => 'Color de la moto',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el color',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/'.$constants[ 'FILTER_FIELD_COLOR' ].'/'.$constants[ 'MOTORCYCLES' ],
                ],
                
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_MILEAGE' ],
                    'label' => 'Kilometraje Recorrido',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_YEAR' ],
                    'label' => 'Año del Vehiculo',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_ENGINE_DISPLACEMENT' ],
                    'label' => 'Desplazamiento del motor',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_NUMBER_OF_CYLINDERS' ],
                    'label' => 'Número de cilindros',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'visible' => true,
                    'field' => $constants[ 'FILTER_FIELD_CONDITION_TYPE' ],
                    'label' => 'Tipo de Condición',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de publicación',
                    'values' =>
                    [
                        [
                            'value' => 'venta',
                            'text' => 'Venta',
                        ]
                    ],
                ],
            ],
        ],
    ],

];
