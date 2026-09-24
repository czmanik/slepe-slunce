<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Services\MailchimpSubscriberSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'name' => ['nullable', 'string', 'max:160'],
            'new_expeditions' => ['nullable', 'boolean'],
            'project_news' => ['nullable', 'boolean'],
            'shop_news' => ['nullable', 'boolean'],
            'expeditions' => ['nullable', 'array'],
            'expeditions.*' => ['integer', 'exists:expeditions,id'],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);
        $topicsSelected = $request->boolean('new_expeditions') || $request->boolean('project_news')
            || $request->boolean('shop_news') || count($data['expeditions'] ?? []) > 0;
        if (! $topicsSelected) {
            return back()->withErrors(['topics' => 'Vyberte alespoň jedno téma.'])->withInput();
        }

        $subscriber = Subscriber::query()->firstOrNew(['email' => mb_strtolower($data['email'])]);
        $subscriber->fill([
            'name' => $data['name'] ?? null,
            'status' => 'pending',
            'new_expeditions' => $request->boolean('new_expeditions'),
            'project_news' => $request->boolean('project_news'),
            'shop_news' => $request->boolean('shop_news'),
            'confirm_token' => Str::random(64),
            'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(64),
            'confirmed_at' => null,
            'unsubscribed_at' => null,
            'consent_at' => now(),
            'consent_ip' => $request->ip(),
            'source' => 'public-web',
        ])->save();
        $subscriber->expeditions()->sync($data['expeditions'] ?? []);

        $confirmationUrl = route('subscriptions.confirm', $subscriber->confirm_token);
        Mail::raw(
            "Potvrďte odběr novinek projektu Slepé Slunce:\n\n{$confirmationUrl}\n\nPokud jste se nepřihlásili, zprávu ignorujte.",
            fn ($message) => $message->to($subscriber->email)->subject('Potvrďte odběr novinek Slepé Slunce'),
        );

        return back()->with('message', 'Poslali jsme vám potvrzovací odkaz. Odběr začne až po jeho otevření.');
    }

    public function confirm(string $token, MailchimpSubscriberSync $mailchimp): RedirectResponse
    {
        $subscriber = Subscriber::query()->where('confirm_token', $token)->firstOrFail();
        abort_if($subscriber->created_at->lt(now()->subMonth()) && ! $subscriber->confirmed_at, 410, 'Platnost potvrzovacího odkazu vypršela.');
        $subscriber->update(['status' => 'active', 'confirmed_at' => now(), 'confirm_token' => Str::random(64)]);
        try {
            $mailchimp->sync($subscriber->load('expeditions'));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('home')->with('message', 'Odběr novinek je potvrzen. Děkujeme.');
    }

    public function unsubscribe(string $token, MailchimpSubscriberSync $mailchimp): RedirectResponse
    {
        $subscriber = Subscriber::query()->where('unsubscribe_token', $token)->firstOrFail();
        $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
        try {
            $mailchimp->sync($subscriber->load('expeditions'));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('home')->with('message', 'Odběr novinek byl ukončen.');
    }
}
