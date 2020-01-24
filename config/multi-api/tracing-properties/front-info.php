<?php

$constants = config( 'multi-api.tracing-properties.constants' );

return [

    /*
    |--------------------------------------------------------------------------
    | Peru Properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'baseURL' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/tracing_properties/index',
    ],


    'registerProperty' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/tracing_properties/register_property',
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


];
