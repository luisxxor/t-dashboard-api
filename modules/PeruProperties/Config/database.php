<?php

return [
    'connections' => [
        'mongo' => [
            'driver'   => 'mongodb',
            'host'     => env( 'DB_PERU_PROPERTIES_HOST', '127.0.0.1' ),
            'port'     => env( 'DB_PERU_PROPERTIES_PORT', 27017 ),
            'database' => env( 'DB_PERU_PROPERTIES_DATABASE' ),
            'username' => env( 'DB_PERU_PROPERTIES_USERNAME' ),
            'password' => env( 'DB_PERU_PROPERTIES_PASSWORD' ),
            'options'  => [
                // sets the authentication database required by mongo 3
                'database' => env( 'DB_PERU_PROPERTIES_DATABASE' )
            ]
        ]
    ]
];
