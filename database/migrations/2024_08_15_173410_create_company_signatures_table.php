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
        Schema::create('company_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('company_name'); 
            $table->string('phone')->nullable();
            $table->string('email')->nullable(); 
            $table->string('address')->nullable(); 
            $table->string('website')->nullable(); 
            $table->string('signature_path'); 
            $table->double('latitude', 10, 6)->nullable(); 
            $table->double('longitude', 10, 6)->nullable();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_signatures');
    }
};
