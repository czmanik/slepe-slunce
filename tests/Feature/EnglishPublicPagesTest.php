<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnglishPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_public_navigation_and_partner_links(): void
    {
        $this->get('https://www.blindsun.eu/')
            ->assertOk()
            ->assertSee('Choose what you want to follow')
            ->assertSee('Email language')
            ->assertSee('href="https://www.sons.cz/"', false)
            ->assertSee('href="https://odskodnenizauraz.cz/"', false);
    }

    public function test_english_journal_guides_and_map_have_english_interface(): void
    {
        $this->get('https://www.blindsun.eu/denik')
            ->assertOk()
            ->assertSee('Blind Sun journal')
            ->assertDontSee('Deník Slepého slunce');
        $this->get('https://www.blindsun.eu/navody')
            ->assertOk()
            ->assertSee('Accessible travel guides')
            ->assertDontSee('Otevřít návod');
        $this->get('https://www.blindsun.eu/mapa')
            ->assertOk()
            ->assertSee('Explore our expeditions')
            ->assertDontSee('Mapa našich expedic');
    }
}
