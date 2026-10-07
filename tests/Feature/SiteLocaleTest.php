<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_blindsun_domain_uses_english_public_layer(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'blindsun.eu'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Czech initiative, currently based in Estepona, Spain');
    }

    public function test_czech_domain_keeps_czech_public_layer(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'slepeslunce.cz'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="cs">', false)
            ->assertSee('Jsme česká iniciativa se současnou základnou v Esteponě');
    }
}
