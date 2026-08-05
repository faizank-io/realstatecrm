<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('feedback_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('deal_amount', 15, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->enum('status', ['offer_made', 'under_contract', 'closed_won', 'closed_lost'])->default('offer_made');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->nullableMorphs('documentable'); // e.g. Property, Deal, Lead
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('viewings');
    }
};
