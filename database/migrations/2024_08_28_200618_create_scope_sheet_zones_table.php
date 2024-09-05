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
        Schema::create('scope_sheet_zones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scope_sheet_id')->constrained('scope_sheets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('zones')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('zone_order');
            $table->longText('zone_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_sheet_zones');
    }
};
