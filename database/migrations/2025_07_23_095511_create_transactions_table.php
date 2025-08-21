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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->unsignedBigInteger('stock_in_id')->nullable();
            $table->unsignedBigInteger('stock_out_id')->nullable();
            $table->unsignedTinyInteger('cash_flow_id');
            $table->unsignedTinyInteger('tran_type_id');
            $table->unsignedMediumInteger('amount');
            $table->timestamps();
            $table->index('customer_id');
            $table->index('date');
            $table->index('cash_flow_id');
            $table->index('tran_type_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
