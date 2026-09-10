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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->restrictOnDelete();
            $table->foreignId('personnel_id')
                ->nullable()
                ->constrained('personnels', 'personnel_id')
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->string('type')->default('general');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
