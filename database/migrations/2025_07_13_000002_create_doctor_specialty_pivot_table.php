<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorSpecialtyPivotTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_doctor_specialty', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('doctor_id')->unsigned();
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('doctor_specialty_id')->unsigned();
            $table->foreign('doctor_specialty_id')->references('id')->on('doctor_specialties')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['doctor_id', 'doctor_specialty_id', 'company_id'], 'doctor_specialty_unique');
            // $table->unique(['doctor_id', 'doctor_specialty_id'], 'doctor_specialty_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_doctor_specialty');
    }
}
