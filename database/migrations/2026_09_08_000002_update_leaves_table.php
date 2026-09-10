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
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('reason')->nullable(false);
            $table->text('remarks')->nullable()->after('status');
            $table->dropColumn('approved_at');
            $table->dateTime('approved_at')->nullable()->after('remarks');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->after('personnel_id')
                ->nullable()
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
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['user_id', 'approved_by', 'status', 'remarks']);
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->date('approved_at')->nullable()->after('remarks');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();
        });
    }
};
