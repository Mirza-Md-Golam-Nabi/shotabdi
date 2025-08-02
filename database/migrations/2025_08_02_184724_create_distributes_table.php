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
        Schema::create('distributes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedMediumInteger('dokan')->nullable()->default(0);
            $table->unsignedMediumInteger('home')->nullable()->default(0);
            $table->timestamps();
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributes');
    }
};
