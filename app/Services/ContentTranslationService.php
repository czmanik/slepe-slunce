<?php
namespace App\Services;
use App\Models\Concerns\HasContentTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class ContentTranslationService {
 public function __construct(private GoogleCloudTranslationService $translator) {}
 public function translate(Model $model, string $locale='en', bool $fresh=false): void {
  if (! in_array(HasContentTranslations::class, class_uses_recursive($model), true)) throw new \InvalidArgumentException('Model nepodporuje překlady.');
  $existing=$model->contentTranslations()->where('locale',$locale)->first();
  if ($existing && ! $fresh) return;
  $source=[]; foreach ($model::translatableFields() as $field) { $value=$model->getRawOriginal($field); if (filled($value)) $source[$field]=$value; }
  $translated=$this->translator->translate($source,$locale);
  $model->contentTranslations()->updateOrCreate(['locale'=>$locale],['content'=>$translated,'machine_translated_at'=>Carbon::now(),'reviewed_at'=>null]);
 }
}