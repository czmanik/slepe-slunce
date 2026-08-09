<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('route_order')->default(0)->index();
            $table->string('status', 20)->default('planned')->index();
            $table->boolean('is_goal')->default(false)->index();
            $table->dateTime('occurred_at')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_alt', 300)->nullable();
            $table->json('gallery')->nullable();
            $table->json('videos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_points');
    }
};
