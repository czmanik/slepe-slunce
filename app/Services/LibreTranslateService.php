<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LibreTranslateService
{
    public function translate(array $content, string $target = 'en'): array
    {
        $url = rtrim((string) config('services.libretranslate.url'), '/');
        if ($url === '' || ! preg_match('#^https?://#', $url)) {
            throw new RuntimeException('Nastavte interní LIBRETRANSLATE_URL.');
        }

        $translated = [];
        foreach ($content as $field => $value) {
            $response = Http::timeout((int) config('services.libretranslate.timeout', 60))
                ->post($url.'/translate', [
                    'q' => $value,
                    'source' => 'cs',
                    'target' => $target,
                    'format' => in_array($field, ['body', 'description'], true) && str_contains($value, '<') ? 'html' : 'text',
                ])->throw();
            $result = $response->json('translatedText');
            if (! is_string($result) || trim($result) === '') {
                throw new RuntimeException('LibreTranslate vrátil neúplný překlad pole '.$field.'.');
            }
            $translated[$field] = $result;
        }

        return $translated;
    }
}
