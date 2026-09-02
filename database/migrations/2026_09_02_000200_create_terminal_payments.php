<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminal_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expedition_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('transaction_id', 100)->unique();
            $table->string('reference', 100)->index();
            $table->string('status', 20)->default('PENDING')->index();
            $table->integer('amount');
            $table->string('currency', 3);
            $table->json('provider_payload')->nullable();
            $table->dateTime('checked_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_payments');
    }
};
