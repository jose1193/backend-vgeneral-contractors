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
        Schema::create('cause_of_losses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('cause_loss_name');
            $table->text('description')->nullable();
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cause_of_losses');
    }
};
