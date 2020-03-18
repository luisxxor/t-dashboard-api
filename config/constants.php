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

    'PROJECT_ACCESS_REQUESTS_PENDING_STATUS' => 'pending',
    'PROJECT_ACCESS_REQUESTS_APPROVED_STATUS' => 'approved',
    'PROJECT_ACCESS_REQUESTS_DENIED_STATUS' => 'denied',

    'payment_gateways' => [
        'MERCADOPAGO' => 'mercadopago',
    ],

    'payment_currencies' => [
        'PEN' => 'PEN',
        'ECS' => 'ECS',
        'CLP' => 'CLP',
    ],
];
