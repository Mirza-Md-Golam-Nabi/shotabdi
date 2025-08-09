<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('type', 'cash_flow_id');
            $table->unsignedTinyInteger('tran_type_id')->after('cash_flow_id');
            $table->index('tran_type_id');

        });

        DB::table('transactions')->where('cash_flow_id', 'deposit')->update(['cash_flow_id' => 1]);
        DB::table('transactions')->where('cash_flow_id', 'expense')->update(['cash_flow_id' => 2]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedTinyInteger('cash_flow_id')->change();
            $table->index('cash_flow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['cash_flow_id']);

        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('cash_flow_id', 'type');
            $table->dropColumn('tran_type_id');

        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type', 25)->change();
        });

        DB::table('transactions')->where('type', '1')->update(['type' => 'deposit']);
        DB::table('transactions')->where('type', '2')->update(['type' => 'expense']);

    }
};
