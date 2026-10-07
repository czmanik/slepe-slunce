<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class GoogleCloudTranslationService {
 public function translate(array $content, string $target='en'): array {
  $key=config('services.google_translate.key');
  throw_if(blank($key), RuntimeException::class, 'Chybí GOOGLE_TRANSLATE_API_KEY.');
  $keys=array_keys($content); $values=array_values($content);
  if ($values===[]) return [];
  $response=Http::timeout((int) config('services.google_translate.timeout',20))->post('https://translation.googleapis.com/language/translate/v2?key='.urlencode($key), ['q'=>$values,'source'=>'cs','target'=>$target,'format'=>'html'])->throw();
  $translations=data_get($response->json(),'data.translations',[]);
  if (count($translations)!==count($keys)) throw new RuntimeException('Překladová služba vrátila neúplnou odpověď.');
  return collect($keys)->mapWithKeys(fn($field,$i)=>[$field=>htmlspecialchars_decode((string) data_get($translations,$i.'.translatedText'))])->all();
 }
}