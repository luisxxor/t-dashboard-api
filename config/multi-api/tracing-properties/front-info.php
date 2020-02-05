<?php

$constants = config( 'multi-api.tracing-properties.constants' );

return [

    /*
    |--------------------------------------------------------------------------
    | Trancing properties Front-End Info Config
    |--------------------------------------------------------------------------
    |
    */

    'InitPointer' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/tracing_properties/init_pointer',
    ],    

    'registerProperty' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/tracing_properties/create_property',
    ],

    'updateProperty' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/tracing_properties/update_property/{id}',
    ],


    'createTracing' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/tracing/create',
    ],   

    'editTracing' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/tracing/edit/{id}',
    ],    

    'updateTracing' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/tracing/update/{id}',
    ],    

    'deleteTracing' => [
        'method' => 'delete',
        'path' => env( 'APP_URL' ) . '/api/tracing/delete/{id}',
    ],

    'createClient' => [
        'method' => 'post',
        'path' => env( 'APP_URL' ) . '/api/client/create',
    ],   

    'editClient' => [
        'method' => 'get',
        'path' => env( 'APP_URL' ) . '/api/client/edit/{id}',
    ],    

    'updateClient' => [
        'method' => 'patch',
        'path' => env( 'APP_URL' ) . '/api/client/update/{id}',
    ],    

    'deleteClient' => [
        'method' => 'delete',
        'path' => env( 'APP_URL' ) . '/api/client/delete/{id}',
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
