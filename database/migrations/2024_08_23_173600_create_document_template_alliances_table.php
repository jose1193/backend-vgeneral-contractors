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
        Schema::create('document_template_alliances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('signature_path_id')->constrained('company_signatures')->onUpdate('cascade')->onDelete('cascade');
            $table->string('template_name_alliance');
            $table->string('template_description_alliance')->nullable();
            $table->string('template_type_alliance');
            $table->string('template_path_alliance');
            $table->foreignId('alliance_company_id')->constrained('alliance_companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_template_alliances');
    }
};
