<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('expeditions')->updateOrInsert(
            ['slug' => 'chorvatsko-pripravujeme'],
            [
                'name' => 'Chorvatsko: moře bez bariér',
                'short_description' => 'Připravujeme společnou cestu k moři pro vidící, nevidomé a slabozraké cestovatele. Termín a podrobnosti zveřejníme po potvrzení trasy a zázemí.',
                'description' => "Chorvatsko připravujeme jako další společnou expedici Slepého Slunce. Chceme vytvořit cestu, kde je stejně důležitá dobrá organizace, prostor pro vlastní tempo a možnost prožít moře naplno všemi smysly.\n\nPrávě ověřujeme trasu, ubytování, dopravu a podmínky asistence. Zatím proto nesbíráme závazné přihlášky ani nestanovujeme termín nebo cenu. Jakmile budou podmínky potvrzené, zveřejníme je zde a pošleme je odběratelům novinek o expedicích.",
                'timezone' => 'Europe/Prague',
                'publication_status' => 'published',
                'status_override' => 'planned',
                'is_featured' => false,
                'registration_enabled' => false,
                'allowed_registration_modes' => json_encode([]),
                'allowed_payment_methods' => json_encode([]),
                'reservation_hold_hours' => 48,
                'accessibility_details' => 'Před zveřejněním programu ověříme přístupnost dopravy, ubytování, přesunů i možnosti individuální asistence. Důležité informace zveřejníme v přístupné textové podobě.',
                'archive_member_locations' => false,
                'settings' => json_encode(['planned_announcement' => true, 'destination' => 'Croatia']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('expeditions')->where('slug', 'chorvatsko-pripravujeme')->delete();
    }
};
