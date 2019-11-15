<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Properties Dashboard Config
    |--------------------------------------------------------------------------
    |
    */

    'front-info' => [
        'baseURL' => [
            'method' => 'get',
            'path' => env( 'APP_URL' ) . '/api/peru_properties/index',
        ],

        'searchURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_properties/properties_ajax',
        ],

        'paginationURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_properties/properties_paginate',
        ],

        'processPurchaseURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_properties/process_purchase',
        ],

        'exportFileURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_properties/purchase_files/{id}/export',
        ],

        'mapMarkers' => [
            'visible' => true,
            'perpage' => 500,
            'settings' => [
                'initialState' => [
                    'coordinates' => [
                        -9.189966999999998, // lat
                        -75.015152 // lng
                    ],
                    'baseZoom' => 5,
                    'searchZoom' => 15,
                ]
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
                    'field' => 'property_type_id',
                    'label' => 'Tipo',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de propiedad',
                    'valuesURL' => env( 'APP_URL' ) . '/api/peru_properties/index',
                ],
                [
                    'field' => 'publication_date',
                    'label' => 'Fecha',
                    'type' => 'date',
                    'placeholder' => 'Seleccione el tipo de propiedad',
                    'minDate' => null,
                    'maxDate' => now()->toDateString(),
                ],

                [
                    'field' => 'bedrooms_interval',
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
                    'field' => 'bathrooms_interval',
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
                    'field' => 'parkings_interval',
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
                    'field' => 'antiquity_years',
                    'label' => 'Antigüedad (años)',
                    'type' => 'numerical_range',
                    'step' => 1,
                    'minValue' => 0,
                    'maxValue' => null,
                ],
                [
                    'field' => 'build_area_m2',
                    'label' => 'Área exclusiva (m2)',
                    'type' => 'numerical_range',
                    'step' => 0.01,
                    'minValue' => 0.00,
                    'maxValue' => null,
                ],
                [
                    'field' => 'total_area_m2',
                    'label' => 'Área Techada (m2)',
                    'type' => 'numerical_range',
                    'step' => 0.01,
                    'minValue' => 0.00,
                    'maxValue' => null,
                ],
                [
                    'field' => 'publication_type',
                    'label' => 'Tipo publicación',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione el tipo de publicación',
                    'values' =>
                    [
                        [
                            'value' => 'venta',
                            'text' => 'Venta',
                        ],
                        [
                            'value' => 'alquiler',
                            'text' => 'Alquiler',
                        ],
                    ],
                ],
                [
                    'field' => 'property_new',
                    'label' => 'Nuevo/Usado',
                    'type' => 'dropdown',
                    'placeholder' => 'Seleccione la condición',
                    'values' =>
                    [
                        [
                            'value' => true,
                            'text' => 'Nuevo',
                        ],
                        [
                            'value' => false,
                            'text' => 'Usado',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'backend-info' => [
        'code' => 'pe-properties',
        'generate_file_url' => 'peru_properties.generatePropertiesFile',
        'generate_file_url_full' => 'api.peru_properties.generatePropertiesFile',
    ],
];
