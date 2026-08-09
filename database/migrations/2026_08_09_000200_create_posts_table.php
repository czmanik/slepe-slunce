<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('body');
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->date('event_date')->nullable();
            $table->string('location')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('cover_alt')->nullable();
            $table->json('gallery')->nullable();
            $table->json('videos')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('author_post', function (Blueprint $table): void {
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['author_id', 'post_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('author_post'); Schema::dropIfExists('posts'); }
};
