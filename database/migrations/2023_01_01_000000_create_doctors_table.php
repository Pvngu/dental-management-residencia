<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorsTable extends Migration
{
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('doctor_department_id')->unsigned();
            $table->foreign('doctor_department_id')->references('id')->on('doctor_departments')->onDelete('cascade')->onUpdate('cascade');
            $table->string('qualification', 255)->nullable()->default(null);
            $table->string('specialist')->nullable()->default(null);
            $table->string('designation')->nullable()->default(null);
            $table->text('description')->nullable();
            $table->string('practice_id')->nullable()->default(null);
            $table->decimal('appointment_charge', 10, 2);
            $table->decimal('missed_appointment_charge', 10, 2)->nullable()->default(null);
            $table->integer('cancellation_notice_hours')->nullable()->default(24);
            $table->enum('status', ['available', 'busy', 'break'])->nullable();
            $table->string('professional_cedula', 500)->nullable();
            $table->string('color', 7)->nullable()->default('#3B82F6');
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
}
