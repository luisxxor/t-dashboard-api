<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |
    */

    'ORDERS_OPENED_STATUS' => 'opened', // initial status
    'ORDERS_PENDING_STATUS' => 'pending',
    'ORDERS_TO_PAY_STATUS' => 'to_pay',
    'ORDERS_RELEASED_STATUS' => 'released',

    'RECEIPTS' => [
        'STATUS' => [
            'TO_PAY' => 'to_pay', // initial status
            'PENDING' => 'pending',
            'RELEASED' => 'released',
        ],
    ],

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
