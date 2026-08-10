<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table): void {
            $table->string('photo')->nullable()->after('bio');
            $table->string('photo_alt', 300)->nullable()->after('photo');
        });

        Schema::create('expedition_states', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('active');
            $table->boolean('is_manual')->default(false);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('member_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('accuracy_meters')->nullable();
            $table->dateTime('reported_at')->index();
            $table->timestamps();
        });

        Schema::create('map_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('route_point_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('route_segment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image');
            $table->string('alt', 300);
            $table->string('caption', 500)->nullable();
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->dateTime('taken_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_photos');
        Schema::dropIfExists('member_locations');
        Schema::dropIfExists('expedition_states');
        Schema::table('authors', function (Blueprint $table): void {
            $table->dropColumn(['photo', 'photo_alt']);
        });
    }
};
