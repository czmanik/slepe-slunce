<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('guide_topic')->nullable()->index();
        });

        foreach ([
            'pred-cestou' => ['prava-nevidomeho-cestujiciho', 'jak-objednat-asistenci', 'kdyz-asistence-neprijde'],
            'doprava' => ['dalkovy-autobus-asistence', 'vlak-a-asistence', 'letadlo-a-asistence'],
            's-partakem' => ['nekolikadennipobyt-s-partakem', 'vodici-pes-na-cestach'],
        ] as $topic => $slugs) {
            DB::table('posts')
                ->where('category', 'cestovani-bez-barier')
                ->whereIn('slug', $slugs)
                ->update(['guide_topic' => $topic]);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['guide_topic']);
            $table->dropColumn('guide_topic');
        });
    }
};
