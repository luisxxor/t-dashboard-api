<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |
    */

    'ORDERS_RELEASED_STATUS' => 'released',
    'ORDERS_OPENED_STATUS' => 'opened',
    'ORDERS_PENDING_STATUS' => 'pending',
    'ORDERS_TO_PAY_STATUS' => 'to_pay',

    'PROJECT_ACCESS_REQUESTS' => [
        'PENDING_STATUS' => 'pending',
        'APPROVED_STATUS' => 'approved',
        'DENIED_STATUS' => 'denied',
    ],

    'payment_gateways' => [
        'MERCADOPAGO' => 'mercadopago',
    ],

    'payment_currencies' => [
        'PEN' => 'PEN',
        'ECS' => 'ECS',
        'CLP' => 'CLP',
    ],
];
