<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('g_number')->index();
            $table->date('date')->nullable()->index();
            $table->date('last_change_date')->nullable()->index();
            $table->string('supplier_article')->nullable()->index();
            $table->string('tech_size')->nullable();
            $table->bigInteger('barcode')->nullable()->index();
            $table->decimal('total_price', 14, 4)->nullable();
            $table->unsignedSmallInteger('discount_percent')->nullable();
            $table->boolean('is_supply')->nullable();
            $table->boolean('is_realization')->nullable();
            $table->decimal('promo_code_discount', 14, 4)->nullable();
            $table->string('warehouse_name')->nullable();
            $table->string('country_name')->nullable();
            $table->string('oblast_okrug_name')->nullable();
            $table->string('region_name')->nullable();
            $table->unsignedBigInteger('income_id')->nullable()->index();
            $table->string('sale_id')->nullable()->index();
            $table->string('odid')->nullable()->index();
            $table->decimal('spp', 8, 2)->nullable();
            $table->decimal('for_pay', 14, 4)->nullable();
            $table->decimal('finished_price', 14, 4)->nullable();
            $table->decimal('price_with_disc', 14, 4)->nullable();
            $table->bigInteger('nm_id')->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_storno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
