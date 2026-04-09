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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('g_number')->index();
            $table->dateTime('date')->nullable()->index();
            $table->date('last_change_date')->nullable()->index();
            $table->string('supplier_article')->nullable()->index();
            $table->string('tech_size')->nullable();
            $table->unsignedBigInteger('barcode')->nullable()->index();
            $table->decimal('total_price', 14, 4)->nullable();
            $table->unsignedSmallInteger('discount_percent')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->string('oblast')->nullable();
            $table->unsignedBigInteger('income_id')->nullable()->index();
            $table->string('odid')->nullable()->index();
            $table->unsignedBigInteger('nm_id')->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_cancel')->default(false);
            $table->dateTime('cancel_dt')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
