<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_products', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 190);
            $table->string('slug', 190)->unique();
            $table->string('winery', 190)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt', 300)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_archival')->default(true);
            $table->timestamps();
        });
        Schema::create('wine_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wine_product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->unsignedSmallInteger('vintage')->nullable();
            $table->string('bottle_size', 30)->default('0,75 l');
            $table->string('quality', 160)->nullable();
            $table->unsignedSmallInteger('alcohol_percent_x10')->nullable();
            $table->integer('price_czk');
            $table->integer('price_eur')->nullable();
            $table->unsignedTinyInteger('vat_rate')->default(21);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->boolean('is_active')->default(false)->index();
            $table->json('expert_appraisals')->nullable();
            $table->timestamps();
        });
        Schema::create('shop_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 30)->unique();
            $table->string('status', 30)->default('new')->index();
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->string('currency', 3);
            $table->integer('subtotal');
            $table->integer('shipping_total')->default(0);
            $table->integer('grand_total');
            $table->string('customer_name', 190);
            $table->string('email', 190)->index();
            $table->string('phone', 50)->nullable();
            $table->string('billing_street', 250);
            $table->string('billing_city', 160);
            $table->string('billing_postcode', 30);
            $table->string('billing_country', 2)->default('CZ');
            $table->string('delivery_method', 30)->default('pickup');
            $table->text('note')->nullable();
            $table->string('invoice_number', 30)->nullable()->unique();
            $table->string('access_token', 64)->unique();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('consent_at');
            $table->timestamps();
        });
        Schema::create('shop_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 100);
            $table->string('name', 250);
            $table->unsignedInteger('quantity');
            $table->integer('unit_price');
            $table->unsignedTinyInteger('vat_rate');
            $table->integer('total');
            $table->timestamps();
        });
        Schema::create('shop_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30)->default('comgate');
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('status', 30)->default('pending')->index();
            $table->integer('amount');
            $table->string('currency', 3);
            $table->text('redirect_url')->nullable();
            $table->json('provider_payload')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_payments');
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('wine_variants');
        Schema::dropIfExists('wine_products');
    }
};
