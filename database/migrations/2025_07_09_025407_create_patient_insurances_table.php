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
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            
            $table->enum('insurance_type', ['primary', 'secondary'])->default('primary');
            $table->string('insurance_name')->nullable();
            $table->string('insured_ssn')->nullable();
            $table->string('insured_name')->nullable();
            $table->date('insured_dob')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('employer_phone')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Index for better performance
            $table->index(['patient_id', 'insurance_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_insurances');
    }
};
