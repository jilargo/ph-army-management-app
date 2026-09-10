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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id('leave_id');
            $table->foreignId('personnel_id')
                ->constrained('personnels', 'personnel_id')
                ->restrictOnDelete();
            $table->foreignId('leave_type_id')
                ->constrained('leave_types', 'leave_type_id')
                ->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason');
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->restrictOnDelete();
            $table->date('approved_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
