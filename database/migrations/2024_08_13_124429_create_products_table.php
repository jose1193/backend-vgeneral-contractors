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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('product_category_id')->constrained('category_products')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('product_name');
            $table->longText('product_description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('order_position')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
