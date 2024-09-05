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
        Schema::create('file_assignment_esxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files_esxes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('public_adjuster_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_assignment_esxes');
    }
};
