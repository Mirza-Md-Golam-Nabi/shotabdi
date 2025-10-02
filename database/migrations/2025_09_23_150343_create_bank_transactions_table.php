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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('from')->nullable();
            $table->unsignedBigInteger('to')->nullable();
            $table->mediumInteger('amount');
            $table->unsignedBigInteger('tran_id_from')->nullable();
            $table->unsignedBigInteger('tran_id_to')->nullable();
            $table->timestamps();
            $table->index('date');
            $table->index('from');
            $table->index('to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
