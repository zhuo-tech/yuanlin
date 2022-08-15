<?php

return [
    'autoload' => false,
    'hooks' => [
        'config_init' => [
            'alisms',
            'qcloudsms',
        ],
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
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
