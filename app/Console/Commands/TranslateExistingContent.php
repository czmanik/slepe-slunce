<?php
namespace App\Console\Commands;
use App\Models\Expedition; use App\Models\Post; use App\Services\ContentTranslationService; use Illuminate\Console\Command;
class TranslateExistingContent extends Command {
 protected $signature='content:translate-existing {--fresh : Přeloží znovu i existující anglické verze}';
 protected $description='Automaticky přeloží články a expedice z češtiny do angličtiny';
 public function handle(ContentTranslationService $service): int {
  $fresh=(bool)$this->option('fresh'); $done=0;
  foreach ([Post::class,Expedition::class] as $class) $class::query()->orderBy('id')->each(function ($model) use ($service,$fresh,&$done): void { $service->translate($model,'en',$fresh); $done++; $this->line('✓ '.class_basename($model).' #'.$model->id); });
  $this->info("Hotovo: {$done} položek."); return self::SUCCESS;
 }
}