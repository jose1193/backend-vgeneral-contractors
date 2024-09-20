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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('property_id')->constrained('properties')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('signature_path_id')->constrained('company_signatures')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('type_damage_id')->constrained('type_damages')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id_ref_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('claim_number')->nullable();
            $table->string('claim_internal_id');
            $table->string('policy_number');
            $table->string('date_of_loss')->nullable();
            $table->text('description_of_loss')->nullable();
            $table->integer('number_of_floors')->nullable();
            $table->string('claim_date')->nullable();
            $table->string('claim_status')->nullable();
            $table->string('work_date')->nullable();
            $table->text('damage_description')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->boolean('customer_reviewed')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
