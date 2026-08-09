<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_point_id')->constrained('route_points')->cascadeOnDelete();
            $table->foreignId('to_point_id')->constrained('route_points')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 180)->nullable();
            $table->text('description')->nullable();
            $table->string('transport_mode', 30)->index();
            $table->string('status', 30)->default('planned')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->dateTime('scheduled_departure_at')->nullable()->index();
            $table->dateTime('scheduled_arrival_at')->nullable();
            $table->dateTime('departed_at')->nullable()->index();
            $table->dateTime('arrived_at')->nullable();
            $table->decimal('distance_km', 9, 1)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('provider', 160)->nullable();
            $table->string('reference', 160)->nullable();
            $table->string('geometry_mode', 20)->default('automatic');
            $table->json('waypoints')->nullable();
            $table->json('geometry')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_alt', 300)->nullable();
            $table->json('gallery')->nullable();
            $table->json('videos')->nullable();
            $table->timestamps();

            $table->index(['from_point_id', 'to_point_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_segments');
    }
};
