<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            'alisms',
            'qcloudsms',
        ],
        'sms_notice' => [
            'alisms',
            'qcloudsms',
        ],
        'sms_check' => [
            'alisms',
            'qcloudsms',
        ],
        'config_init' => [
            'qcloudsms',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
