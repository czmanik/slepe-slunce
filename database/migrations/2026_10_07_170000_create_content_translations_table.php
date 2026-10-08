<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('content_translations', function (Blueprint $table): void {
  $table->id(); $table->morphs('translatable'); $table->string('locale', 10); $table->json('content'); $table->timestamp('machine_translated_at')->nullable(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps(); $table->unique(['translatable_type','translatable_id','locale'], 'content_translation_locale_unique');
 });}
 public function down(): void { Schema::dropIfExists('content_translations'); }
};