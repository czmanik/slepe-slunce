<?php

namespace Tests\Feature;

use App\Models\Expedition;
use App\Models\RoutePoint;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\WineProduct;
use App\Models\WineVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MultiExpeditionPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_and_current_points_are_isolated_per_expedition(): void
    {
        $legacy = Expedition::default();
        $mikulov = Expedition::query()->create(['name' => 'Mikulov', 'slug' => 'mikulov', 'publication_status' => 'published']);
        RoutePoint::query()->create(['expedition_id' => $legacy->id, 'name' => 'Praha', 'latitude' => 50, 'longitude' => 14, 'status' => 'current']);
        RoutePoint::query()->create(['expedition_id' => $mikulov->id, 'name' => 'Mikulov', 'latitude' => 48, 'longitude' => 16, 'status' => 'current']);

        $this->assertDatabaseHas('route_points', ['expedition_id' => $legacy->id, 'name' => 'Praha', 'status' => 'current']);
        $this->assertDatabaseHas('route_points', ['expedition_id' => $mikulov->id, 'name' => 'Mikulov', 'status' => 'current']);
        $this->get(route('expeditions.route', $mikulov))->assertOk()->assertSee('Mikulov')->assertDontSee('Praha');
    }

    public function test_registration_uses_per_expedition_modes_and_capacity(): void
    {
        $expedition = Expedition::query()->create([
            'name' => 'Ochutnávka vín', 'slug' => 'ochutnavka', 'publication_status' => 'published',
            'registration_enabled' => true, 'allowed_registration_modes' => ['application'],
            'allowed_payment_methods' => ['cash', 'bank_transfer'], 'public_capacity' => 5,
        ]);
        $this->post(route('expeditions.register.store', $expedition), [
            'mode' => 'application', 'payment_method' => 'bank_transfer', 'name' => 'Jan Novák', 'email' => 'jan@example.test', 'party_size' => 2, 'privacy_consent' => '1',
        ])->assertRedirect(route('expeditions.show', $expedition));
        $this->assertDatabaseHas('expedition_registrations', ['expedition_id' => $expedition->id, 'party_size' => 2, 'status' => 'new', 'payment_method' => 'bank_transfer']);
    }

    public function test_valtice_prototypes_are_ready_and_card_payment_stays_disabled(): void
    {
        $expeditions = Expedition::query()->where('settings->prototype', true)->orderBy('start_at')->get();

        $this->assertCount(3, $expeditions);
        $this->assertSame(['2026-09-05', '2026-09-26', '2026-10-17'], $expeditions->map(fn (Expedition $expedition): string => $expedition->start_at->toDateString())->all());
        $this->assertTrue($expeditions->every(fn (Expedition $expedition): bool => $expedition->programItems()->count() === 5));

        $expedition = $expeditions->first();
        $this->get(route('expeditions.show', $expedition))
            ->assertOk()
            ->assertSee('Návrh pro testovací provoz')
            ->assertSee('Co je v ceně');
        $this->get(route('expeditions.register', $expedition))
            ->assertOk()
            ->assertSee('Hotově na místě')
            ->assertSee('Bankovním převodem')
            ->assertSee('Platební kartou')
            ->assertSee('zatím není aktivní');

        $this->post(route('expeditions.register.store', $expedition), [
            'mode' => 'reservation', 'payment_method' => 'card', 'name' => 'Jan Novák',
            'email' => 'jan@example.test', 'party_size' => 1, 'privacy_consent' => '1',
        ])->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('expedition_registrations', ['email' => 'jan@example.test']);
    }

    public function test_subscription_requires_confirmation_and_records_topics(): void
    {
        Mail::fake();
        $this->from('/')->post(route('subscriptions.store'), ['email' => 'news@example.test', 'project_news' => '1', 'privacy_consent' => '1'])->assertRedirect('/');
        $subscriber = Subscriber::query()->firstOrFail();
        $this->assertSame('pending', $subscriber->status);
        $this->get(route('subscriptions.confirm', $subscriber->confirm_token))->assertRedirect(route('home'));
        $this->assertSame('active', $subscriber->fresh()->status);
    }

    public function test_guest_can_order_available_wine_and_stock_is_reserved(): void
    {
        config(['shop.comgate.merchant' => null, 'shop.comgate.secret' => null]);
        $product = WineProduct::query()->create(['name' => 'Ryzlink', 'slug' => 'ryzlink', 'is_active' => true]);
        $variant = WineVariant::query()->create(['wine_product_id' => $product->id, 'sku' => 'R-2008', 'vintage' => 2008, 'price_czk' => 125000, 'stock_quantity' => 3, 'is_active' => true]);
        $this->post(route('shop.cart.add', $variant), ['quantity' => 1, 'age_confirmed' => '1'])->assertRedirect(route('shop.cart'));
        $response = $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Jan Novák', 'email' => 'jan@example.test', 'billing_street' => 'Hlavní 1', 'billing_city' => 'Praha',
            'billing_postcode' => '11000', 'billing_country' => 'CZ', 'age_confirmed' => '1', 'terms' => '1', 'privacy_consent' => '1',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('shop_orders', ['email' => 'jan@example.test', 'grand_total' => 125000]);
        $this->assertSame(1, $variant->fresh()->reserved_quantity);
    }

    public function test_admin_can_open_new_management_screens(): void
    {
        $admin = User::query()->create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'test-password', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/expeditions/create')->assertOk();
        $this->actingAs($admin)->get('/admin/program-items/create')->assertOk();
        $this->actingAs($admin)->get('/admin/expedition-registrations')->assertOk();
        $this->actingAs($admin)->get('/admin/wine-products/create')->assertOk();
        $this->actingAs($admin)->get('/admin/shop-orders')->assertOk();
    }
}
