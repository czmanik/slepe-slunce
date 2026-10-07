<?php

namespace Tests\Feature;

use App\Http\Middleware\SetSiteLocale;
use Illuminate\Http\Request;
use Tests\TestCase;

class SiteLocaleTest extends TestCase
{
    public function test_blindsun_domain_selects_english_locale(): void
    {
        $request = Request::create('https://blindsun.eu/');

        app(SetSiteLocale::class)->handle($request, fn () => response('ok'));

        $this->assertSame('en', app()->getLocale());
    }

    public function test_czech_domain_selects_czech_locale(): void
    {
        $request = Request::create('https://slepeslunce.cz/');

        app(SetSiteLocale::class)->handle($request, fn () => response('ok'));

        $this->assertSame('cs', app()->getLocale());
    }
}
