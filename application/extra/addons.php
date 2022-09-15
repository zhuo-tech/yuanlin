<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            'alisms',
        ],
        'sms_notice' => [
            'alisms',
        ],
        'sms_check' => [
            'alisms',
        ],
        'upgrade' => [
            'simditor',
        ],
        'config_init' => [
            'simditor',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
