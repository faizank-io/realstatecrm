<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('cascade');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('guard_name')->default('web');
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['agency_id', 'name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. properties.create, properties.view, leads.manage
            $table->string('display_name')->nullable();
            $table->string('module')->default('general'); // e.g. properties, leads, viewings, deals, team
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
