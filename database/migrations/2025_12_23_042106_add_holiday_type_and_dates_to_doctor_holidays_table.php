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
        Schema::table('doctor_holidays', function (Blueprint $table) {
            $table->string('holiday_type')->nullable()->default('vacation')->after('reason');
            $table->date('start_date')->nullable()->after('holiday_type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('status')->default('pending')->after('end_date');
            
            // Drop the old date column (will be replaced by start_date and end_date)
            // Uncomment if you want to remove the old date column
            // $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_holidays', function (Blueprint $table) {
            $table->dropColumn(['holiday_type', 'start_date', 'end_date', 'status']);
            
            // Re-add date column if you dropped it
            // $table->date('date')->nullable();
        });
    }
};
