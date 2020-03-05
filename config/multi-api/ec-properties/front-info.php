<?php

$constants = config( 'multi-api.ec-properties.constants' );

return [

    /*
    |--------------------------------------------------------------------------
    | Ecuador Properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'ghostSearchURL' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/ghost_search',
    ],

    'searchURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/search',
    ],

    'paginationURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/paginate',
    ],

    'processOrderURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/order',
    ],

    'paymentTypes' => [
        [
            'paymentType' => config( 'constants.payment_gateways.MERCADOPAGO' ),
            'currency' => config( 'constants.payment_currencies.ECS' )
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trancing properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'InitPointer' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing_properties/init_pointer',
    ],    

    'registerProperty' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing_properties/create_property',
    ],

    'updateProperty' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing_properties/update_property/{id}',
    ],


    'createTracing' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing/create',
    ],   

    'editTracing' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing/edit/{id}',
    ],    

    'updateTracing' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing/update/{id}',
    ],    

    'deleteTracing' => [
        'method' => 'delete',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/tracing/delete/{id}',
    ],

    'createClient' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/client/create',
    ],   

    'editClient' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/client/edit/{id}',
    ],    

    'updateClient' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/client/update/{id}',
    ],    

    'deleteClient' => [
        'method' => 'delete',
        'path' => env( 'APP_URL' ) . '/api/ecuador_properties/client/delete/{id}',
    ],

    'mapMarkers' => [
        'visible' => true,
        'perpage' => 500,
        'settings' => [
            'initialState' => [
                'coordinates' => [ 
                    -2.077494458211648, // lat
                    -78.9539790551534 // lng
                ],
                'baseZoom' => 5,
                'searchZoom' => 15,
            ]
        ]
    ],

    'currencies' => [
        [
            'currency'=> 'Dolar Americanos',
            'symbol'=> '$',
            'field'=> 'dollars_price'
        
        ]
    ],

    'containerItems' => [
        'visible' => true,
        'perpage' => 10,
    ],

    'filters' => [
        'visible' => true,
        'fields' => [
            [
                'field' => $constants[ 'FILTER_FIELD_PROPERTY_TYPE' ],
                'label' => 'Tipo',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de propiedad',
                'valuesURL' => env( 'APP_URL' ) . '/api/ecuador_properties/filters/property_type',
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                'label' => 'Tipo',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de propiedad',
                'valuesURL' => env( 'APP_URL' ) . '/api/ecuador_properties/filters/publication_type',
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_DATE' ],
                'label' => 'Fecha',
                'type' => 'date',
                'placeholder' => 'Seleccione el tipo de propiedad',
                'minDate' => null,
                'maxDate' => now()->toDateString(),
            ],

            [
                'field' => $constants[ 'FILTER_FIELD_BEDROOMS' ],
                'label' => 'Habitaciones',
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
                'field' => $constants[ 'FILTER_FIELD_BATHROOMS' ],
                'label' => 'Baños',
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
                'field' => $constants[ 'FILTER_FIELD_PARKINGS' ],
                'label' => 'Cocheras',
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
                'field' => $constants[ 'FILTER_FIELD_ANTIQUITY_YEARS' ],
                'label' => 'Antigüedad (años)',
                'type' => 'numerical_range',
                'step' => 1,
                'minValue' => 0,
                'maxValue' => null,
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_TOTAL_AREA_M2' ],
                'label' => 'Área exclusiva (m2)',
                'type' => 'numerical_range',
                'step' => 0.01,
                'minValue' => 0.00,
                'maxValue' => null,
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_BUILD_AREA_M2' ],
                'label' => 'Área Techada (m2)',
                'type' => 'numerical_range',
                'step' => 0.01,
                'minValue' => 0.00,
                'maxValue' => null,
            ],
        ],
    ],

];
