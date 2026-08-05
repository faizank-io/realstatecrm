<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['group', 'key', 'agency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
