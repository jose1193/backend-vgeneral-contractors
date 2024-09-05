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
        Schema::create('scope_sheet_presentations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scope_sheet_id')->constrained('scope_sheets')->onUpdate('cascade')->onDelete('cascade');
            $table->string('photo_type'); 
            $table->string('photo_path');
            $table->integer('photo_order');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_sheet_presentations');
    }
};
