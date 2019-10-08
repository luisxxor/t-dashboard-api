<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Vehicles Dashboard Config
    |--------------------------------------------------------------------------
    |
    */

    'front-info' => [
        'baseURL' => [
            'method' => 'get',
            'path' => env( 'APP_URL' ) . '/api/peru_vehicles/index',
        ],

        'searchURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_vehicles/ajax',
        ],

        'paginationURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_vehicles/paginate',
        ],

        'processPurchaseURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_vehicles/process_purchase',
        ],

        'exportFileURL' => [
            'method' => 'post',
            'path' => env( 'APP_URL' ) . '/api/peru_vehicles/purchase_files/{id}/export',
        ],

        'mapMarkers' => [
            'visible' => false,
        ],

        'containerItems' => [
            'visible' => true,
            'perpage' => 10,
        ],

        'filters' => [
            'visible' => true,
            'fields' => [],
        ],
    ],

    'backend-info' => [
        'code' => 'pe-vehicles',
        'generate_file_url' => 'peru_vehicles.generateFile',
        'generate_file_url_full' => 'api.peru_vehicles.generateFile',
    ],
];
