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
        Schema::table('checkins', function (Blueprint $table) {
            $table->string('overtime_status')->nullable()->after('approved_by');
            $table->foreignId('overtime_approved_by')->nullable()->after('overtime_status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropForeign(['overtime_approved_by']);
            $table->dropColumn(['overtime_status', 'overtime_approved_by']);
        });
    }
};
