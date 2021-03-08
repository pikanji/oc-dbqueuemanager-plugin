<?php

return [
    'plugin' => [
        'name' => 'DB Queue Manager',
        'description' => 'Show jobs in database managed queue',
        'author' => 'pikanji',
    ],
    'settings' => [
        'jobs' => [
            'label' => 'Background Jobs',
            'description' => 'Display pending jobs',
            'category' => 'System Status',
            'permission_label' => 'View background jobs',
        ],
    ],
    'model' => [
        'job' => [
            'field' => [
                'id' => 'ID',
                'queue' => 'Queue',
                'payload' => 'Payload',
                'attempts' => 'Attempts',
                'available_at' => 'Available At',
                'created_at' => 'Created At',
            ],
        ],
    ],
];
