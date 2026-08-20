<?php

return [
    'mailchimp' => [
        'key' => env('MAILCHIMP_API_KEY'),
        'server' => env('MAILCHIMP_SERVER'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
    ],
    'routing' => [
        'base_url' => env('ROUTING_BASE_URL', 'https://router.project-osrm.org'),
        'timeout' => env('ROUTING_TIMEOUT', 12),
    ],
];
