<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_subscription_sends_english_confirmation_with_english_domain(): void
    {
        Mail::fake();

        $this->post('https://www.blindsun.eu/odber', [
            'email' => 'reader@example.test',
            'name' => 'Reader',
            'locale' => 'en',
            'project_news' => 1,
            'privacy_consent' => 1,
        ])->assertRedirect()->assertSessionHas('message', 'We sent you a confirmation link. Your subscription starts when you open it.');

        $subscriber = Subscriber::query()->firstOrFail();
        $this->assertSame('en', $subscriber->locale);

        Mail::assertSentCount(1);
    }
}
