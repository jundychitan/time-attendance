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
            $table->string('manual_time_out_status')->nullable()->after('manual_time_out');
            $table->foreignId('approved_by')->nullable()->after('manual_time_out_status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['manual_time_out_status', 'approved_by']);
        });
    }
};
