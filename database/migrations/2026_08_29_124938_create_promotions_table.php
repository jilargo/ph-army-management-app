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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->foreignId('personnel_id')
                ->constrained('personnels', 'personnel_id')
                ->restrictOnDelete();
            $table->foreignId('from_rank_id')
                ->constrained('ranks', 'rank_id')
                ->restrictOnDelete();
            $table->foreignId('to_rank_id')
                ->constrained('ranks', 'rank_id')
                ->restrictOnDelete();
            $table->date('promotion_date');
            $table->string('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
