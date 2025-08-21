<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedSmallInteger('product_id');
            $table->unsignedSmallInteger('quantity');
            $table->decimal('rate', 8, 2);
            $table->unsignedSmallInteger('discount')->nullable()->default(0);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->index('date');
            $table->index('customer_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
