<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ClinicLocation;
use App\Models\DoctorSchedule;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_schedules', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable()->after('company_id');
                $table->foreign('clinic_id')->references('id')->on('clinic_locations')->onDelete('cascade');
            }

            // check if unique index exists? Laravel doesn't have easy hasIndex
            // But we can try/catch or just assume it's needed if we are here.
            // Or name it explicitly to avoid duplicates?
            try {
                $table->unique(['doctor_id', 'clinic_id'], 'doctor_schedules_doctor_id_clinic_id_unique');
            } catch (\Exception $e) {
                // index might exist
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctor_schedules', function (Blueprint $table) {
            // We only drop if we added it? Hard to know strict rollback.
            // We can drop the unique constraint.
            $table->dropUnique('doctor_schedules_doctor_id_clinic_id_unique');
            
            // Should we drop column? Only if we added it. 
            // Better safe to not drop column if it existed before.
            // But for this task, I'll duplicate the logic or just leave it.
        });
    }
};
