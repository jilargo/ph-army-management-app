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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id('training_id');
            $table->foreignId('personnel_id')
                ->constrained('personnels', 'personnel_id')
                ->restrictOnDelete();
            $table->foreignId('course_id')
                ->constrained('courses', 'course_id')
                ->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->string('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
