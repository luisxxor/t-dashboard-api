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
        'visible' => true,
        'fields' => [
            [
                'field' => $constants[ 'FILTER_FIELD_PUBLICATION_TYPE' ],
                'label' => 'Tipo de Busqueda',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de busqueda',
                'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/publication_type',
            ],
            [
                'field' => $constants[ 'FILTER_FIELD_MAKE_TYPE' ],
                'label' => 'Marca de Vehiculo',
                'type' => 'dropdown',
                'placeholder' => 'Seleccione el tipo de marca',
                'valuesURL' => env( 'APP_URL' ) . '/api/peru_vehicles/filters/make_type/{publication_type}',
            ],
            [
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

];
