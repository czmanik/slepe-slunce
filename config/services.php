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
    'google_translate' => [
        'key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'timeout' => (int) env('GOOGLE_TRANSLATE_TIMEOUT', 20),
    ],
    'sentry' => [
        'browser_loader_url' => env('SENTRY_BROWSER_LOADER_URL'),
        'browser_traces_sample_rate' => (float) env('SENTRY_BROWSER_TRACES_SAMPLE_RATE', 0.1),
    ],
];
