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
        Mail::shouldReceive('raw')->once()->withArgs(function (string $body, callable $configure): bool {
            $this->assertStringContainsString('Confirm your Blind Sun updates subscription:', $body);
            $this->assertStringContainsString('https://www.blindsun.eu/', $body);
            $this->assertStringContainsString('/odber/', $body);

            $message = \Mockery::mock(\Illuminate\Mail\Message::class);
            $message->shouldReceive('to')->once()->with('reader@example.test')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('Confirm your Blind Sun updates')->andReturnSelf();
            $configure($message);

            return true;
        });

        $this->post('https://www.blindsun.eu/odber', [
            'email' => 'reader@example.test',
            'name' => 'Reader',
            'locale' => 'en',
            'project_news' => 1,
            'privacy_consent' => 1,
        ])->assertRedirect()->assertSessionHas('message', 'We sent you a confirmation link. Your subscription starts when you open it.');

        $subscriber = Subscriber::query()->firstOrFail();
        $this->assertSame('en', $subscriber->locale);

    }
}
