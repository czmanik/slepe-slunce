<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Veřejné domény
    |--------------------------------------------------------------------------
    |
    | Host blindsun.eu slouží anglickou vrstvu téhož Laravel projektu.
    | Česká doména zůstává zdrojem původního obsahu.
    |
    */
    'czech_url' => env('CZECH_SITE_URL', 'https://slepeslunce.cz'),
    'english_url' => env('ENGLISH_SITE_URL', 'https://www.blindsun.eu'),
    'english_hosts' => array_filter(array_map(
        'trim',
        explode(',', env('ENGLISH_SITE_HOSTS', 'blindsun.eu,www.blindsun.eu'))
    )),
];
