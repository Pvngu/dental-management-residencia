<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cleanup appointments table:
     * 1. Remove redundant 'notes' column (keep 'appointment_notes')
     * 2. Remove 'status_appointment' column (status derived from datetime fields)
     * 3. Update 'status' enum to simplified values for booking states only
     * 4. Backfill status based on datetime fields for data consistency
     */
    public function up(): void
    {
        // Step 1: Backfill status based on datetime fields before modifying structure
        // This ensures data consistency: if datetime fields indicate completion, update status
        DB::statement("
            UPDATE appointments 
            SET status = 'completed' 
            WHERE checkout_datetime IS NOT NULL 
               OR completed_datetime IS NOT NULL
        ");

        DB::statement("
            UPDATE appointments 
            SET status = 'confirmed' 
            WHERE status = 'in-progress' 
              AND completed_datetime IS NULL
        ");

        // Step 2: Remove redundant columns
        Schema::table('appointments', function (Blueprint $table) {
            // Remove unused 'notes' column (appointment_notes is the active field)
            if (Schema::hasColumn('appointments', 'notes')) {
                $table->dropColumn('notes');
            }

            // Remove 'status_appointment' - frontend derives status from datetime fields
            if (Schema::hasColumn('appointments', 'status_appointment')) {
                $table->dropColumn('status_appointment');
            }
        });

        // Step 3: Modify status enum to simplified booking states
        // Values: pending (awaiting confirmation), confirmed (scheduled), cancelled, completed (done)
        // Remove 'in-progress' and 'delayed' - these are now tracked via datetime fields
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'confirmed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original enum with all values
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'in-progress', 'cancelled', 'completed', 'delayed') DEFAULT 'confirmed'");

        Schema::table('appointments', function (Blueprint $table) {
            // Restore status_appointment column
            $table->string('status_appointment')->nullable()->after('treatment_type_id');
            
            // Restore notes column
            $table->text('notes')->nullable()->after('treatment_details');
        });

        // Backfill status_appointment from datetime fields
        DB::statement("
            UPDATE appointments SET status_appointment = 
                CASE 
                    WHEN checkout_datetime IS NOT NULL THEN 'checked_out'
                    WHEN completed_datetime IS NOT NULL THEN 'completed'
                    WHEN in_progress_datetime IS NOT NULL THEN 'in_progress'
                    WHEN checkin_datetime IS NOT NULL OR arrive_datetime IS NOT NULL THEN 'checked_in'
                    ELSE 'scheduled'
                END
        ");
    }
};
