<?php

use App\Enums\FeedDisburseEnum;
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
        Schema::create('feed_disburses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_out_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedSmallInteger('product_id');
            $table->date('previous_date')->nullable();
            $table->date('next_date')->nullable();
            $table->tinyInteger('status')->default(FeedDisburseEnum::Pending);
            $table->timestamps();
            $table->index('stock_out_id');
            $table->index('next_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_disburses');
    }
};
