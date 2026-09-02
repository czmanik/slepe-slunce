<?php

return [
    // Obchod není součástí veřejného webu. Zapíná se pouze pro přihlášené
    // správce, kteří potřebují provést technický test katalogu a Comgate.
    'testing_enabled' => env('SHOP_TESTING_ENABLED', false),
    'seller' => ['name' => env('SHOP_SELLER_NAME', "Heaven's Mill CZ s.r.o."), 'company_id' => env('SHOP_SELLER_ICO', '04561929'), 'vat_id' => env('SHOP_SELLER_DIC')],
    'comgate' => ['merchant' => env('COMGATE_MERCHANT'), 'secret' => env('COMGATE_SECRET'), 'test' => env('COMGATE_TEST', true)],
];
