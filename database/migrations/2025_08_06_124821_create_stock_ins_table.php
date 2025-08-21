<?php

use App\Enums\AvailableEnum;
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
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedSmallInteger('product_id');
            $table->unsignedSmallInteger('quantity');
            $table->decimal('rate', 8, 2);
            $table->unsignedSmallInteger('discount')->nullable()->default(0);
            $table->decimal('amount', 12, 2);
            $table->unsignedTinyInteger('is_available')->default(AvailableEnum::INACTIVE->value)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('customer_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
