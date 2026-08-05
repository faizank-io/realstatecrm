<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable()->after('id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->after('agency_id')->constrained('roles')->onDelete('set null');
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default('active')->after('password'); // active, inactive, suspended
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['agency_id', 'role_id', 'phone', 'status']);
        });
    }
};
