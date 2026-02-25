<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_type')->nullable()->after('user_type');
        });

        // Set role_type for Doctors
        DB::table('users')
            ->whereIn('id', function($query) {
                $query->select('user_id')->from('doctors');
            })
            ->update(['role_type' => 'doctor']);

        // Set role_type for Patients
        DB::table('users')
            ->whereIn('id', function($query) {
                $query->select('user_id')->from('patients');
            })
            ->update(['role_type' => 'patient']);

        // Set role_type for Staff (assuming 'staff_members' user_type and remaining nulls)
        DB::table('users')
            ->where('user_type', 'staff_members')
            ->whereNull('role_type')
            ->update(['role_type' => 'staff']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_type');
        });
    }
};
