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
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('promotion_date');
            $table->text('recommendation')->nullable()->after('remarks');
            $table->datetime('approved_at')->nullable()->after('recommendation');
            $table->foreignId('recommended_by')
                ->nullable()
                ->after('to_rank_id')
                ->constrained('users', 'id')
                ->nullOnDelete();
            $table->foreignId('approved_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users', 'id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['recommended_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'recommendation', 'approved_at', 'recommended_by', 'approved_by']);
        });
    }
};
