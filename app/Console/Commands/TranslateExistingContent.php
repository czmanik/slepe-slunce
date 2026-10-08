<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Models\Post;
use App\Services\ContentTranslationService;
use Illuminate\Console\Command;
use Throwable;

class TranslateExistingContent extends Command
{
    protected $signature = 'content:translate-existing {--fresh : Přepíše také schválené překlady}';
    protected $description = 'Přeloží nové a změněné články a expedice z češtiny do angličtiny';

    public function handle(ContentTranslationService $service): int
    {
        $done = 0;
        $failed = 0;
        foreach ([Post::class, Expedition::class] as $class) {
            $class::query()->orderBy('id')->chunkById(50, function ($models) use ($service, &$done, &$failed): void {
                foreach ($models as $model) {
                    try {
                        if ($service->translate($model, 'en', (bool) $this->option('fresh'))) {
                            $done++;
                            $this->line('✓ '.class_basename($model).' #'.$model->id);
                        }
                    } catch (Throwable $error) {
                        $failed++;
                        $this->error(class_basename($model).' #'.$model->id.': '.$error->getMessage());
                    }
                }
            });
        }

        $this->info("Přeloženo: {$done}, chyby: {$failed}.");
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
