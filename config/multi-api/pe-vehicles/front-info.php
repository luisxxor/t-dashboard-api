<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

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

];
