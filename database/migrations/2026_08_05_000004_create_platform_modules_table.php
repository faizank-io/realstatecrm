<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. properties, crm_leads, viewings, deals, documents, analytics
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_addon')->default(false);
            $table->decimal('addon_price_monthly', 10, 2)->default(0);
            $table->decimal('addon_price_yearly', 10, 2)->default(0);
            $table->json('included_in_plans')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_modules');
    }
};
