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
        // First, truncate the table to remove existing data
        DB::table('patient_insurances')->truncate();
        
        Schema::table('patient_insurances', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('patient_insurances', 'insurance_type')) {
                $table->dropColumn('insurance_type');
            }
            if (Schema::hasColumn('patient_insurances', 'insurance_name')) {
                $table->dropColumn('insurance_name');
            }
            if (Schema::hasColumn('patient_insurances', 'insured_ssn')) {
                $table->dropColumn('insured_ssn');
            }
            if (Schema::hasColumn('patient_insurances', 'insured_name')) {
                $table->dropColumn('insured_name');
            }
            if (Schema::hasColumn('patient_insurances', 'insured_dob')) {
                $table->dropColumn('insured_dob');
            }
            if (Schema::hasColumn('patient_insurances', 'employer_name')) {
                $table->dropColumn('employer_name');
            }
            if (Schema::hasColumn('patient_insurances', 'employer_phone')) {
                $table->dropColumn('employer_phone');
            }
        });
        
        Schema::table('patient_insurances', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('patient_insurances', 'provider_id')) {
                $table->bigInteger('provider_id')->unsigned()->after('patient_id');
                $table->foreign('provider_id')->references('id')->on('insurance_providers')->onDelete('cascade')->onUpdate('cascade');
            }
            
            if (!Schema::hasColumn('patient_insurances', 'policy_holder_name')) {
                $table->string('policy_holder_name')->after('provider_id');
            }
            if (!Schema::hasColumn('patient_insurances', 'relationship_to_holder')) {
                $table->enum('relationship_to_holder', ['self', 'spouse', 'child', 'other'])->default('self')->after('policy_holder_name');
            }
            if (!Schema::hasColumn('patient_insurances', 'member_id')) {
                $table->string('member_id')->nullable()->after('relationship_to_holder');
            }
            if (!Schema::hasColumn('patient_insurances', 'group_number')) {
                $table->string('group_number')->nullable()->after('member_id');
            }
            if (!Schema::hasColumn('patient_insurances', 'plan_type')) {
                $table->string('plan_type')->nullable()->after('group_number');
            }
            if (!Schema::hasColumn('patient_insurances', 'is_primary')) {
                $table->boolean('is_primary')->default(true)->after('plan_type');
            }
            if (!Schema::hasColumn('patient_insurances', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('is_primary');
            }
            
            // Try to add index - will fail silently if it already exists
            try {
                $table->index(['patient_id', 'is_primary']);
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_insurances', function (Blueprint $table) {
            // Check and drop foreign key if exists
            if (Schema::hasColumn('patient_insurances', 'provider_id')) {
                try {
                    $table->dropForeign(['provider_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn(['provider_id', 'policy_holder_name', 'relationship_to_holder', 'member_id', 'group_number', 'plan_type', 'is_primary', 'verified_at']);
            }
            
            // Restore old columns if they don't exist
            if (!Schema::hasColumn('patient_insurances', 'insurance_type')) {
                $table->enum('insurance_type', ['primary', 'secondary'])->default('primary');
            }
            if (!Schema::hasColumn('patient_insurances', 'insurance_name')) {
                $table->string('insurance_name')->nullable();
            }
            if (!Schema::hasColumn('patient_insurances', 'insured_ssn')) {
                $table->string('insured_ssn')->nullable();
            }
            if (!Schema::hasColumn('patient_insurances', 'insured_name')) {
                $table->string('insured_name')->nullable();
            }
            if (!Schema::hasColumn('patient_insurances', 'insured_dob')) {
                $table->date('insured_dob')->nullable();
            }
            if (!Schema::hasColumn('patient_insurances', 'employer_name')) {
                $table->string('employer_name')->nullable();
            }
            if (!Schema::hasColumn('patient_insurances', 'employer_phone')) {
                $table->string('employer_phone')->nullable();
            }
        });
    }
};
