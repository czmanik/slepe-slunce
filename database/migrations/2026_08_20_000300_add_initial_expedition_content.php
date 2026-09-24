<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('expeditions')
            ->where('slug', 'slepe-slunce-2026')
            ->where(fn ($query) => $query->whereNull('description')->orWhere('description', ''))
            ->update([
                'description' => "První expedice Slepého Slunce vede z Prahy přes Barcelonu do španělského vnitrozemí za úplným zatměním Slunce. Není to jen cesta za výjimečným okamžikem na obloze. Je to společná výprava vidících a nevidomých přátel, při které zkoušíme, kolik věcí lze opravdu vidět jinak.\n\nDen po dni zaznamenáváme cestu, setkání, nečekané situace i obyčejné chvíle, ze kterých se expedice skládá. V deníku najdete texty členů výpravy, fotografie, videa a postupně také trasu jednotlivých etap.",
                'accessibility_details' => 'Obsah expedice připravujeme s důrazem na přístupnost pro nevidomé a slabozraké návštěvníky. Fotografie mají textové popisy a důležité informace zveřejňujeme i v textové podobě.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Obsah mohl být po nasazení redakčně upraven, proto jej automaticky nemažeme.
    }
};
