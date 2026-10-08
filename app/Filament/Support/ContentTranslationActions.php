<?php

namespace App\Filament\Support;

use App\Jobs\TranslateContent;
use App\Models\Concerns\HasContentTranslations;
use App\Services\ContentTranslationService;
use App\Support\HtmlSanitizer;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ContentTranslationActions
{
    public static function for(Model $record): array
    {
        if (! in_array(HasContentTranslations::class, class_uses_recursive($record), true)) {
            return [];
        }

        $fields = $record::translatableFields();
        return [
            Action::make('translateEnglish')
                ->label('Vygenerovat anglický překlad')
                ->requiresConfirmation()
                ->modalDescription('Překlad se zpracuje na pozadí. Schválený překlad lze přepsat pouze volbou v příkazu Artisan.')
                ->action(function () use ($record): void {
                    TranslateContent::dispatch($record);
                    Notification::make()->title('Překlad zařazen do fronty')->success()->send();
                }),
            Action::make('editEnglish')
                ->label('Upravit anglický překlad')
                ->fillForm(fn (): array => $record->contentTranslations()->where('locale', 'en')->first()?->content ?? [])
                ->form(array_map(fn (string $field) => $field === 'body'
                    ? RichEditor::make($field)
                        ->label('Obsah (EN)')
                        ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'undo', 'redo'])
                        ->columnSpanFull()
                    : Textarea::make($field)
                        ->label($field.' (EN)')
                        ->rows($field === 'description' ? 8 : 3)
                        ->columnSpanFull(), $fields))
                ->action(function (array $data) use ($record, $fields): void {
                    $content = array_intersect_key($data, array_flip($fields));
                    if (isset($content['body'])) {
                        $content['body'] = app(HtmlSanitizer::class)->sanitize($content['body']);
                    }
                    $record->contentTranslations()->updateOrCreate(['locale' => 'en'], [
                        'content' => $content,
                        'source_hash' => app(ContentTranslationService::class)->sourceHash($record),
                        'reviewed_at' => now(),
                    ]);
                    Notification::make()->title('Anglický překlad uložen')->success()->send();
                }),
        ];
    }
}
