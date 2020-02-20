<?php

$constants = config( 'multi-api.cl-properties.constants' );

return [

    /*
    |--------------------------------------------------------------------------
    | Chile Properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'ghostSearchURL' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/chile_properties/ghost_search',
    ],

    'searchURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/chile_properties/search',
    ],

    'paginationURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/chile_properties/paginate',
    ],

    'processOrderURL' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/chile_properties/order',
    ],

    'paymentTypes' => [
        [
            'paymentType' => config( 'constants.payment_gateways.MERCADOPAGO' ),
            'currency' => config( 'constants.payment_currencies.CLP' )
        ],
    ],

    'mapMarkers' => [
        'visible' => true,
        'perpage' => 500,
        'settings' => [
            'initialState' => [
                'coordinates' => [ 
                    -33.45307514551685, // lat
                    -71.58479621040554 // lng
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
                'field' => $constants[ 'FILTER_FIELD_PROPERTY_TYPE' ],
                'label' => 'Tipo',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de propiedad',
                'valuesURL' => env( 'APP_URL' ) . '/api/chile_properties/filters/property_type',
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                'label' => 'Tipo de publicación',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de publicación',
                'valuesURL' => env( 'APP_URL' ) . '/api/ecuador_properties/filters/publication_type',
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_DATE' ],
                'label' => 'Fecha',
                'type' => 'date',
                'placeholder' => 'Seleccione la fecha',
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
