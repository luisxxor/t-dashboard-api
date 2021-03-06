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

    'PLAN_SUBSCRIPTIONS' => [
        'STATUS' => [
            'TO_PAY' => 'to_pay', // initial status
            'PENDING' => 'pending',
            'RELEASED' => 'released',
        ],
    ],

    'PROJECT_ACCESS_REQUESTS' => [
        'STATUS' => [
            'PENDING' => 'pending', // initial status
            'APPROVED' => 'approved',
            'DENIED' => 'denied',
        ],
    ],

    'payment_gateways' => [
        'MERCADOPAGO' => 'mercadopago',
    ],

    'payment_currencies' => [
        'PEN' => 'PEN',
        'ECS' => 'ECS',
        'CLP' => 'CLP',
    ],

    'STATUS_LABELS' => [
            'opened' => 'Abierto',
            'to_pay' => 'Por pagar',
            'pending' => 'Pendiente',
            'released' => 'Liberado',
            'approved' => 'Aprovado',
            'denied' => 'Denegado',
    ],
];
