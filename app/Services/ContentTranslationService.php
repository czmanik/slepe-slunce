<?php

namespace App\Services;

use App\Models\Concerns\HasContentTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ContentTranslationService
{
    public function __construct(private LibreTranslateService $translator) {}

    public function source(Model $model): array
    {
        if (! in_array(HasContentTranslations::class, class_uses_recursive($model), true)) {
            throw new InvalidArgumentException('Model nepodporuje překlady.');
        }

        $source = [];
        foreach ($model::translatableFields() as $field) {
            $value = $model->getRawOriginal($field);
            if (is_string($value) && trim($value) !== '') {
                $source[$field] = $value;
            }
        }

        return $source;
    }

    public function sourceHash(Model $model): string
    {
        return hash('sha256', json_encode($this->source($model), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function translate(Model $model, string $locale = 'en', bool $fresh = false): bool
    {
        $existing = $model->contentTranslations()->where('locale', $locale)->first();
        $hash = $this->sourceHash($model);
        if ($existing && ! $fresh && ($existing->reviewed_at || $existing->source_hash === $hash)) {
            return false;
        }

        $source = $this->source($model);
        if ($source === []) {
            return false;
        }

        $translated = $this->translator->translate($source, $locale);
        $model->contentTranslations()->updateOrCreate(['locale' => $locale], [
            'content' => $translated,
            'source_hash' => $hash,
            'machine_translated_at' => Carbon::now(),
            'reviewed_at' => null,
        ]);

        return true;
    }
}
