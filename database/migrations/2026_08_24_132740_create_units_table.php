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
        Schema::create('units', function (Blueprint $table) {
            $table->id('unit_id');
            $table->foreignId('parent_id')
                ->constrained('parent_units', 'parent_id')
                ->restrictOnDelete();
            $table->string('unit_name');
            $table->string('unit_code')->unique();
            $table->string('location')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
