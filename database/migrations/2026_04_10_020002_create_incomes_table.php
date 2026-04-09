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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('income_id')->index();
            $table->string('number')->nullable();
            $table->date('date')->nullable()->index();
            $table->date('last_change_date')->nullable()->index();
            $table->string('supplier_article')->nullable()->index();
            $table->string('tech_size')->nullable();
            $table->unsignedBigInteger('barcode')->nullable()->index();
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('total_price', 14, 4)->nullable();
            $table->date('date_close')->nullable()->index();
            $table->string('warehouse_name')->nullable();
            $table->unsignedBigInteger('nm_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
