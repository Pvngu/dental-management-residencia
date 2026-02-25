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
        $tables = [
            'appointments',
            'patient_check_ins',
            'prescriptions',
            'dental_treat_monitors',
            'open_cases',
            'invoices',
            'sales',
            'expenses',
            'payment_transcations', // Note typo in table name
            'inventory_adjustments',
            'purchase_medicines',
            'attendances',
            'doctor_schedules'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('clinic_id')->nullable()->after('id');
                    $table->foreign('clinic_id')->references('id')->on('clinic_locations')->onDelete('set null');
                });
            }
        }

        // Users table: default_clinic_id
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_clinic_id')->nullable()->after('id');
                $table->foreign('default_clinic_id')->references('id')->on('clinic_locations')->onDelete('set null');
            });
        }

        // Patients table: home_clinic_id
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->unsignedBigInteger('home_clinic_id')->nullable()->after('id');
                $table->foreign('home_clinic_id')->references('id')->on('clinic_locations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'appointments',
            'patient_check_ins',
            'prescriptions',
            'dental_treat_monitors',
            'open_cases',
            'invoices',
            'sales',
            'expenses',
            'payment_transcations',
            'inventory_adjustments',
            'purchase_medicines',
            'attendances',
            'doctor_schedules'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['clinic_id']);
                    $table->dropColumn('clinic_id');
                });
            }
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['default_clinic_id']);
                $table->dropColumn('default_clinic_id');
            });
        }

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropForeign(['home_clinic_id']);
                $table->dropColumn('home_clinic_id');
            });
        }
    }
};
