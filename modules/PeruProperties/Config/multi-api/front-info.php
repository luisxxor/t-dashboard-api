<?php

$constants = config( 'multi-api.pe-properties.constants' );
$baseUrl = env( 'APP_URL' ) . '/api/pe-properties';

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'filtersURL' => [
        'method' => 'get',
        'path' => $baseUrl . '/filters',
    ],

    'searchURL' => [
        'method' => 'post',
        'path' => $baseUrl . '/search',
    ],

    'countSearchURL' => [
        'method' => 'get',
        'path' => $baseUrl . '/count',
    ],

    'paginationURL' => [
        'method' => 'get',
        'path' => $baseUrl . '/paginate',
    ],

    'processOrderURL' => [
        'method' => 'post',
        'path' => $baseUrl . '/order',
    ],

    'paymentTypes' => [
        [
            'paymentType' => config( 'constants.payment_gateways.MERCADOPAGO' ),
            'currency' => config( 'constants.payment_currencies.PEN' )
        ],
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
                'baseZoom' => 6,
                'searchZoom' => 15,
            ]
        ]
    ],

    'currencies' => [
        [
            'label' => 'Dolares Americanos',
            'symbol' => '$',
            'code' => 'USD',
            'field' => 'dollars_price'
        ],
        [
            'label' => 'Soles Peruanos',
            'symbol' => 'S/.',
            'code' => 'PEN',
            'field' => 'others_price'
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
                'values' => [ 'remote' ],
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_DATE' ],
                'label' => 'Fecha',
                'type' => 'date',
                'placeholder' => 'Seleccione un rango de fechas',
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
                        'value' => 5,
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
                        'value' => 5,
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
                        'value' => 5,
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
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                'label' => 'Tipo publicación',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de publicación',
                'values' => [ 'remote' ],
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_IS_NEW' ],
                'label' => 'Nuevo/Usado',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione la condición',
                'values' => [
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

];
