<?php
namespace App\Models\Concerns;
use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
trait HasContentTranslations {
 public function contentTranslations(): MorphMany { return $this->morphMany(ContentTranslation::class, 'translatable'); }
 public function getAttribute($key): mixed {
  $value = parent::getAttribute($key);
  if (! is_string($key) || ! app()->isLocale('en') || app()->runningInConsole() || request()->is('admin*') || ! in_array($key, static::translatableFields(), true)) return $value;
  $translation = $this->relationLoaded('contentTranslations') ? $this->contentTranslations->firstWhere('locale','en') : $this->contentTranslations()->where('locale','en')->first();
  return $translation && array_key_exists($key, $translation->content ?? []) ? $translation->content[$key] : $value;
 }
}