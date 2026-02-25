<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorScheduleDaysTable extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('doctor_id')->unsigned()->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('schedule_id')->unsigned()->nullable();
            $table->foreign('schedule_id')->references('id')->on('doctor_schedules')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('day_of_week');
            $table->time('available_from');
            $table->time('available_to');
            $table->boolean('status')->default(1);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedule_days');
    }
}
