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
        Schema::create('claim_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_request_id')->constrained('service_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_services');
    }
};
