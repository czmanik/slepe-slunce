<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('expeditions', 'allowed_payment_methods')) {
            Schema::table('expeditions', function (Blueprint $table): void {
                $table->json('allowed_payment_methods')->nullable()->after('allowed_registration_modes');
            });
        }
        if (! Schema::hasColumn('expedition_registrations', 'payment_method')) {
            Schema::table('expedition_registrations', function (Blueprint $table): void {
                $table->string('payment_method', 30)->nullable()->after('payment_status')->index();
            });
        }

        $common = [
            'description' => "Komorní víkend ve Valticích propojí víno, společný čas a přístupné cestování. Nejde o odbornou zkoušku ani o soutěž v poznávání odrůd. Chceme vytvořit prostor, kde lze víno objevovat chutí, vůní, příběhem i rozhovorem a kde se vidící a nevidomí účastníci přirozeně potkají u jednoho stolu.\n\nProgram počítá s komentovanou ochutnávkou, procházkou po Valticích, návštěvou sklepa a dostatkem času na odpočinek. Konkrétní vinařství a finální skladbu vín potvrdíme po domluvě s partnery.",
            'timezone' => 'Europe/Prague',
            'publication_status' => 'published',
            'registration_enabled' => true,
            'allowed_registration_modes' => json_encode(['reservation']),
            'allowed_payment_methods' => json_encode(['cash', 'bank_transfer']),
            'capacity' => 12,
            'public_capacity' => 10,
            'price_czk' => 2490,
            'reservation_hold_hours' => 72,
            'minimum_participants' => 6,
            'leader_name' => 'Tým Slepého Slunce',
            'contact_email' => 'info@slepeslunce.cz',
            'departure_details' => 'Sraz v sobotu v 10:00 ve Valticích. Přesné místo a bezbariérovou trasu od nádraží pošleme potvrzeným účastníkům.',
            'transport_details' => 'Do Valtic je možné přijet vlakem nebo autem. V přihlášce lze uvést místo odjezdu a zájem o spolujízdu; společnou dopravu připravíme podle složení skupiny.',
            'accommodation_details' => 'Počítáme s jednou nocí ve dvoulůžkových pokojích ve Valticích nebo blízkém okolí. Konkrétní ubytování a případný doplatek budou potvrzeny před závaznou rezervací.',
            'accessibility_details' => 'Program připravujeme pro společnou účast vidících, nevidomých a slabozrakých hostů. Před cestou ověříme přístup do sklepa, trasu, osvětlení i možnost asistence. V přihlášce lze popsat individuální potřeby bez nutnosti sdílet zdravotní diagnózu.',
            'included_services' => 'Organizace programu, komentovaná ochutnávka, drobné občerstvení k vínu, místní program a koordinace asistence. Doprava a ubytování budou potvrzeny samostatně podle zvolené varianty.',
            'cancellation_terms' => 'Jde o testovací návrh. Rezervace vznikne až po osobním potvrzení pořadatelem. Finální cenu, způsob úhrady a storno podmínky obdrží účastník před závazným potvrzením.',
            'archive_member_locations' => false,
            'settings' => json_encode(['prototype' => true, 'theme' => 'wine_weekend']),
        ];

        $expeditions = [
            [
                'name' => 'Valtice: Víno všemi smysly',
                'slug' => 'valtice-vino-vsemi-smysly-2026-09',
                'short_description' => 'Přístupný víkend s komorní ochutnávkou, příběhy vinařů a společným objevováním Valtic.',
                'start_at' => '2026-09-05 10:00:00', 'end_at' => '2026-09-06 14:00:00',
                'registration_closes_at' => '2026-09-02 20:00:00',
                'program' => ['Seznámení a orientace ve Valticích', 'Oběd a smyslová procházka městem', 'Komentovaná ochutnávka vín', 'Večerní setkání bez programu', 'Nedělní návštěva sklepa a společné zakončení'],
            ],
            [
                'name' => 'Valtice: Podzim ve sklepě',
                'slug' => 'valtice-podzim-ve-sklepe-2026-09',
                'short_description' => 'Víkend o víně bez bariér: sklep, podzimní Valtice, vůně, chutě a rozhovory u jednoho stolu.',
                'start_at' => '2026-09-26 10:00:00', 'end_at' => '2026-09-27 14:00:00',
                'registration_closes_at' => '2026-09-23 20:00:00',
                'program' => ['Příjezd, přivítání a praktická orientace', 'Podzimní procházka a společný oběd', 'Návštěva sklepa a řízená degustace', 'Večerní příběhy vín a expedic', 'Klidné nedělní dopoledne a rozloučení'],
            ],
            [
                'name' => 'Valtice: Příběhy ročníků',
                'slug' => 'valtice-pribehy-rocniku-2026-10',
                'short_description' => 'Třetí komorní víkend propojí archivní vína, lidské příběhy a praktickou zkušenost s přístupným cestováním.',
                'start_at' => '2026-10-17 10:00:00', 'end_at' => '2026-10-18 14:00:00',
                'registration_closes_at' => '2026-10-14 20:00:00',
                'program' => ['Setkání skupiny a uvedení do víkendu', 'Valtice hmatem, zvukem a vyprávěním', 'Ochutnávka vybraných ročníků', 'Večer s příběhy projektu Slepé Slunce', 'Zpětná vazba, další plány a odjezd'],
            ],
        ];

        foreach ($expeditions as $item) {
            $program = $item['program'];
            unset($item['program']);
            DB::table('expeditions')->updateOrInsert(
                ['slug' => $item['slug']],
                $common + $item + ['updated_at' => now(), 'created_at' => now()]
            );
            $expeditionId = DB::table('expeditions')->where('slug', $item['slug'])->value('id');
            $startsAt = new DateTimeImmutable($item['start_at']);
            $times = ['+0 hours', '+2 hours', '+5 hours', '+9 hours', '+1 day +1 hour'];
            foreach ($program as $index => $title) {
                DB::table('program_items')->updateOrInsert(
                    ['expedition_id' => $expeditionId, 'kind' => 'activity', 'sort_order' => ($index + 1) * 10],
                    [
                        'title' => $title,
                        'description' => 'Orientační bod programu; přesný čas a místo doplníme po potvrzení partnerů.',
                        'starts_at' => $startsAt->modify($times[$index])->format('Y-m-d H:i:s'),
                        'is_public' => true,
                        'updated_at' => now(), 'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('expeditions')->whereIn('slug', [
            'valtice-vino-vsemi-smysly-2026-09',
            'valtice-podzim-ve-sklepe-2026-09',
            'valtice-pribehy-rocniku-2026-10',
        ])->pluck('id');
        DB::table('program_items')->whereIn('expedition_id', $ids)->delete();
        DB::table('expeditions')->whereIn('id', $ids)->delete();
        Schema::table('expedition_registrations', fn (Blueprint $table) => $table->dropColumn('payment_method'));
        Schema::table('expeditions', fn (Blueprint $table) => $table->dropColumn('allowed_payment_methods'));
    }
};
