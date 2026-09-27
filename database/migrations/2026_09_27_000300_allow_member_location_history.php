<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('member_locations', 'member_locations_expedition_id_user_id_unique')) {
            Schema::table('member_locations', fn (Blueprint $table) => $table->dropUnique('member_locations_expedition_id_user_id_unique'));
        }
        Schema::table('member_locations', fn (Blueprint $table) => $table->index(['expedition_id', 'reported_at'], 'member_locations_expedition_reported_index'));
    }

    public function down(): void
    {
        Schema::table('member_locations', fn (Blueprint $table) => $table->dropIndex('member_locations_expedition_reported_index'));
        // Archiv může obsahovat více hlášení od téhož člena. Unikátní omezení nelze bezpečně obnovit.
    }
};
