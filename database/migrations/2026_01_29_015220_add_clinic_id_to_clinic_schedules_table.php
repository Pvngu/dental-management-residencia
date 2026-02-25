<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ClinicLocation;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clinic_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('clinic_schedules', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable()->after('company_id');
                $table->foreign('clinic_id')->references('id')->on('clinic_locations')->onDelete('cascade');
            }

            // Check if we should enforce unique constraint.
            // If existing data has duplicates for null clinic_id, this might fail unless we handle it.
            // But 'null' is unique? No. 
            // Composite unique index allows multiple NULLs in MySQL by default.
            // However, we want to enforce unique per clinic.
            
            // Removing any potential old unique constraint if strictly needed, but assuming none exists on day_of_week alone except maybe combined with company_id?
            // The previous migration didn't add unique constraints.
            
            $table->unique(['clinic_id', 'day_of_week'], 'clinic_schedules_clinic_day_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clinic_schedules', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropUnique('clinic_schedules_clinic_day_unique');
            $table->dropColumn('clinic_id');
        });
    }
};
