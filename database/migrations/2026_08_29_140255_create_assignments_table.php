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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id('assignment_id');

            $table->foreignId('personnel_id')
                ->constrained('personnels', 'personnel_id')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units', 'unit_id')
                ->restrictOnDelete();

            $table->foreignId('rank_id')
                ->constrained('ranks', 'rank_id')
                ->restrictOnDelete();

            $table->string('position')->nullable();

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
