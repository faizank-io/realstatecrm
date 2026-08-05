<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('property_type', ['residential', 'commercial'])->default('residential');
            $table->enum('category', ['apartment', 'house', 'villa', 'office', 'retail', 'land', 'warehouse'])->default('house');
            $table->enum('listing_type', ['sale', 'rent'])->default('sale');
            $table->enum('status', ['draft', 'active', 'under_contract', 'sold', 'rented'])->default('active');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('area_sqft', 10, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('amenities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('properties');
    }
};
