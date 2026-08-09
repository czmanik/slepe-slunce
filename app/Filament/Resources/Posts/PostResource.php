<?php

namespace App\Filament\Resources\Posts;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $modelLabel = 'příspěvek';
    protected static ?string $pluralModelLabel = 'příspěvky';
    protected static ?string $navigationLabel = 'Deník';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Příspěvek')->schema([
                TextInput::make('title')->label('Název')->required()->maxLength(160),
                TextInput::make('slug')->label('Adresa')->helperText('Když necháte prázdné, vytvoří se z názvu.')->unique(ignoreRecord: true)->maxLength(180),
                Textarea::make('excerpt')->label('Krátký úvod')->required()->rows(3)->maxLength(500)->columnSpanFull(),
                RichEditor::make('body')->label('Obsah')->required()->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'undo', 'redo']),
            ])->columns(2),

            Section::make('Autorství a zařazení')->schema([
                Select::make('authors')->label('Autor nebo spoluautoři')->relationship('authors', 'name')->multiple()->preload()->searchable()->required(),
                Grid::make(2)->schema([
                    DatePicker::make('event_date')->label('Datum události')->native(false),
                    TextInput::make('location')->label('Místo')->maxLength(160),
                ]),
            ]),

            Section::make('Publikace')->schema([
                Select::make('status')->label('Stav')->options(fn (): array => auth()->user()?->canPublish() ? PostStatus::options() : [PostStatus::Draft->value => PostStatus::Draft->label()])->required()->default(PostStatus::Draft->value),
                DateTimePicker::make('published_at')->label('Datum a čas zveřejnění')->seconds(false)->native(false)->helperText('Pro naplánovaný nebo publikovaný příspěvek je datum povinné.'),
            ])->columns(2),

            Section::make('Hlavní fotografie')->description('Alternativní text popisuje smysl fotografie člověku, který ji nevidí.')->schema([
                FileUpload::make('cover_image')->label('Fotografie')->image()->disk('public')->directory('posts/covers')->visibility('public')->maxSize(12288)->required(),
                Textarea::make('cover_alt')->label('Alternativní text')->required()->rows(2)->maxLength(300),
            ])->columns(2),

            Section::make('Galerie')->schema([
                Repeater::make('gallery')->label('Fotografie')->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['caption'] ?? 'Fotografie')->schema([
                    FileUpload::make('path')->label('Soubor')->image()->disk('public')->directory('posts/gallery')->visibility('public')->maxSize(12288)->required(),
                    Textarea::make('alt')->label('Alternativní text')->required()->rows(2)->maxLength(300),
                    TextInput::make('caption')->label('Popisek')->maxLength(300),
                ])->columns(2)->columnSpanFull(),
            ])->collapsed(),

            Section::make('YouTube videa')->schema([
                Repeater::make('videos')->label('Videa')->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Video')->schema([
                    TextInput::make('url')->label('YouTube odkaz')->url()->regex('/^https?:\\/\\/(?:www\\.)?(?:youtube(?:-nocookie)?\\.com|youtu\\.be)\\//i')->required()->placeholder('https://www.youtube.com/watch?v=…'),
                    TextInput::make('title')->label('Přístupný název videa')->required()->maxLength(180),
                    Textarea::make('description')->label('Stručný popis obsahu')->rows(2)->required()->maxLength(500),
                    Textarea::make('transcript')->label('Přepis nebo odkaz na přepis')->rows(4)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ])->collapsed(),

            Section::make('Vyhledávače a sdílení')->schema([
                TextInput::make('seo_title')->label('SEO titulek')->maxLength(70),
                Textarea::make('seo_description')->label('SEO popis')->rows(2)->maxLength(170),
            ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Název')->searchable()->sortable()->wrap(),
            TextColumn::make('authors.name')->label('Autoři')->badge(),
            TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn (PostStatus $state): string => $state->label())
                ->color(fn (PostStatus $state): string => match ($state) { PostStatus::Published => 'success', PostStatus::Scheduled => 'warning', PostStatus::Archived => 'gray', default => 'info' }),
            TextColumn::make('published_at')->label('Zveřejnění')->dateTime('j. n. Y H:i')->sortable(),
            TextColumn::make('updated_at')->label('Upraveno')->since()->sortable()->toggleable(),
        ])->filters([
            SelectFilter::make('status')->label('Stav')->options(PostStatus::options()),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        return $user && ! $user->canPublish() ? $query->where('created_by', $user->id) : $query;
    }

    public static function getPages(): array
    {
        return ['index' => ListPosts::route('/'), 'create' => CreatePost::route('/create'), 'edit' => EditPost::route('/{record}/edit')];
    }
}
