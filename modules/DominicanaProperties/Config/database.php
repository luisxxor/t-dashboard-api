<?php

return [
    'connections' => [
        'mongo' => [
            'driver'   => 'mongodb',
            'host'     => env( 'DB_DOMINICANA_PROPERTIES_HOST', '127.0.0.1' ),
            'port'     => env( 'DB_DOMINICANA_PROPERTIES_PORT', 27017 ),
            'database' => env( 'DB_DOMINICANA_PROPERTIES_DATABASE' ),
            'username' => env( 'DB_DOMINICANA_PROPERTIES_USERNAME' ),
            'password' => env( 'DB_DOMINICANA_PROPERTIES_PASSWORD' ),
            'options'  => [
                // sets the authentication database required by mongo 3
                'database' => /*env( 'DB_DOMINICANA_PROPERTIES_DATABASE' )*/'admin' # TODO: cambiar al poner las credenciales correctas
            ]
        ]
    ]
];
