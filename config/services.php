<?php

return [
    'routing' => [
        'base_url' => env('ROUTING_BASE_URL', 'https://router.project-osrm.org'),
        'timeout' => env('ROUTING_TIMEOUT', 12),
    ],
];
