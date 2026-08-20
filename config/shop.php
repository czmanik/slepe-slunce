<?php

return [
    'domain' => env('SHOP_DOMAIN', 'shop.slepeslunce.cz'),
    'seller' => ['name' => env('SHOP_SELLER_NAME', "Heaven's Mill CZ s.r.o."), 'company_id' => env('SHOP_SELLER_ICO', '04561929'), 'vat_id' => env('SHOP_SELLER_DIC')],
    'comgate' => ['merchant' => env('COMGATE_MERCHANT'), 'secret' => env('COMGATE_SECRET'), 'test' => env('COMGATE_TEST', true)],
];
