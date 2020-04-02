<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |
    */

    'ORDERS' => [
        'STATUS' => [
            'OPENED' => 'opened', // initial status
            'TO_PAY' => 'to_pay',
            'PENDING' => 'pending',
            'RELEASED' => 'released',
        ],
    ],

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
