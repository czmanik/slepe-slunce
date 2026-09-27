<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_photos', fn (Blueprint $table) => $table->string('short_story', 280)->nullable());
    }

    public function down(): void
    {
        Schema::table('map_photos', fn (Blueprint $table) => $table->dropColumn('short_story'));
    }
};
