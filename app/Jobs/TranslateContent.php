<?php

namespace App\Jobs;

use App\Services\ContentTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    public function __construct(public Model $record, public bool $fresh = false) {}

    public function handle(ContentTranslationService $service): void
    {
        $service->translate($this->record->fresh(), 'en', $this->fresh);
    }
}
