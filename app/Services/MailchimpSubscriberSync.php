<?php

namespace App\Services;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Http;

class MailchimpSubscriberSync
{
    public function configured(): bool
    {
        return filled(config('services.mailchimp.key')) && filled(config('services.mailchimp.server')) && filled(config('services.mailchimp.list_id'));
    }

    public function sync(Subscriber $subscriber): void
    {
        if (! $this->configured()) {
            return;
        }

        $hash = md5(mb_strtolower($subscriber->email));
        $base = 'https://'.config('services.mailchimp.server').'.api.mailchimp.com/3.0/lists/'.config('services.mailchimp.list_id').'/members/'.$hash;
        Http::withBasicAuth('slepe-slunce', config('services.mailchimp.key'))->timeout(15)->put($base, [
            'email_address' => $subscriber->email,
            'status_if_new' => $subscriber->status === 'active' ? 'subscribed' : 'unsubscribed',
            'status' => $subscriber->status === 'active' ? 'subscribed' : 'unsubscribed',
            'merge_fields' => ['FNAME' => $subscriber->name ?: ''],
        ])->throw();

        $tags = collect([
            $subscriber->project_news ? 'Projekt' : null,
            $subscriber->new_expeditions ? 'Nové expedice' : null,
            ...$subscriber->expeditions->map(fn ($expedition) => 'Expedice: '.$expedition->name),
        ])->filter()->map(fn (string $name): array => ['name' => $name, 'status' => 'active'])->values()->all();
        if ($tags) {
            Http::withBasicAuth('slepe-slunce', config('services.mailchimp.key'))->timeout(15)->post($base.'/tags', ['tags' => $tags])->throw();
        }

        $subscriber->updateQuietly(['mailchimp_synced_at' => now()]);
    }
}
