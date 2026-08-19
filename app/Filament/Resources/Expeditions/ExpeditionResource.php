<?php

namespace App\Filament\Resources\Expeditions;

use App\Enums\ExpeditionStatus;
use App\Enums\RegistrationMode;
use App\Filament\Resources\Expeditions\Pages\CreateExpedition;
use App\Filament\Resources\Expeditions\Pages\EditExpedition;
use App\Filament\Resources\Expeditions\Pages\ListExpeditions;
use App\Models\Expedition;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ExpeditionResource extends Resource
{
    protected static ?string $model = Expedition::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'expedice';

    protected static ?string $pluralModelLabel = 'expedice';

    protected static string|UnitEnum|null $navigationGroup = 'Expedice';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Základní informace')->schema([
                TextInput::make('name')->label('Název')->required()->maxLength(180),
                TextInput::make('slug')->label('Adresa')->required()->unique(ignoreRecord: true)->maxLength(190),
                Textarea::make('short_description')->label('Krátký popis')->rows(3)->maxLength(500)->columnSpanFull(),
                Textarea::make('description')->label('Podrobný popis')->rows(8)->columnSpanFull(),
                DateTimePicker::make('start_at')->label('Začátek')->seconds(false)->native(false)->required(),
                DateTimePicker::make('end_at')->label('Konec')->seconds(false)->native(false)->required()->after('start_at'),
                Select::make('publication_status')->label('Zveřejnění')->options(['draft' => 'Koncept', 'published' => 'Veřejná'])->required()->default('draft'),
                Select::make('status_override')->label('Ruční stav')->options(ExpeditionStatus::options())->placeholder('Automaticky podle data'),
                Checkbox::make('is_featured')->label('Hlavní expedice na úvodní stránce'),
            ])->columns(2),
            Section::make('Přihlášky a kapacita')->schema([
                Checkbox::make('registration_enabled')->label('Přijímat přihlášky'),
                Select::make('allowed_registration_modes')->label('Nabízené typy formuláře')->multiple()->options(RegistrationMode::options()),
                TextInput::make('capacity')->label('Celková kapacita')->integer()->minValue(1),
                TextInput::make('public_capacity')->label('Místa pro veřejnost')->integer()->minValue(1),
                DateTimePicker::make('registration_opens_at')->label('Přihlášky od')->seconds(false)->native(false),
                DateTimePicker::make('registration_closes_at')->label('Přihlášky do')->seconds(false)->native(false),
                TextInput::make('price_czk')->label('Cena CZK')->numeric()->prefix('Kč'),
                TextInput::make('price_eur')->label('Cena EUR')->numeric()->prefix('€'),
                TextInput::make('reservation_hold_hours')->label('Rezervace platby (hodin)')->integer()->default(48)->required(),
                TextInput::make('minimum_participants')->label('Minimální počet účastníků')->integer()->minValue(1),
            ])->columns(2),
            Section::make('Organizace')->schema([
                TextInput::make('leader_name')->label('Vedoucí expedice')->maxLength(160),
                TextInput::make('contact_email')->label('Kontaktní e-mail')->email(),
                TextInput::make('contact_phone')->label('Kontaktní telefon')->tel(),
                Textarea::make('departure_details')->label('Nástupní místa')->rows(3),
                Textarea::make('transport_details')->label('Doprava')->rows(4),
                Textarea::make('accommodation_details')->label('Ubytování')->rows(4),
                Textarea::make('accessibility_details')->label('Přístupnost a asistence')->rows(4),
                Textarea::make('included_services')->label('Co je v ceně')->rows(4),
                Textarea::make('cancellation_terms')->label('Storno podmínky')->rows(4),
            ])->columns(2),
            Section::make('Tým expedice')->schema([
                Repeater::make('memberships')->relationship()->schema([
                    Select::make('author_id')->label('Člen')->relationship('author', 'name')->required()->searchable()->preload()->distinct(),
                    TextInput::make('role')->label('Role')->maxLength(120),
                    Checkbox::make('is_leader')->label('Vedoucí expedice'),
                    TextInput::make('sort_order')->label('Pořadí')->integer()->default(0)->required(),
                    Textarea::make('expedition_bio')->label('Představení pro tuto expedici')->rows(3)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ]),
            Section::make('Poloha a archiv')->schema([
                Checkbox::make('archive_member_locations')->label('Uchovat polohy jako archiv trasy'),
                TextInput::make('location_retention_days')->label('Jinak smazat polohy po dnech')->integer()->minValue(1),
                FileUpload::make('hero_image')->label('Hlavní fotografie')->image()->disk('public')->directory('expeditions'),
                Textarea::make('hero_alt')->label('Alternativní text fotografie')->rows(2)->requiredWith('hero_image')->maxLength(300),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Expedice')->searchable()->sortable(),
            TextColumn::make('computed_status')->label('Stav')->state(fn (Expedition $record) => $record->status()->label())->badge(),
            TextColumn::make('start_at')->label('Začátek')->dateTime('j. n. Y')->sortable(),
            TextColumn::make('end_at')->label('Konec')->dateTime('j. n. Y')->sortable(),
            TextColumn::make('publication_status')->label('Publikace')->badge(),
            IconColumn::make('registration_enabled')->label('Přihlášky')->boolean(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListExpeditions::route('/'), 'create' => CreateExpedition::route('/create'), 'edit' => EditExpedition::route('/{record}/edit')];
    }
}
