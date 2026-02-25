<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('patient_id')->unsigned();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('doctor_id')->unsigned();
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('appointment_date');
            $table->integer('duration');
            $table->text('treatment_details')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in-progress', 'cancelled', 'completed', 'delayed'])->default('confirmed');
            $table->bigInteger('room_id')->unsigned()->nullable();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null')->onUpdate('cascade');
            $table->bigInteger('treatment_type_id')->unsigned()->nullable();
            $table->foreign('treatment_type_id')->references('id')->on('treatment_types')->onDelete('set null')->onUpdate('cascade');
            $table->string('status_appointment')->nullable();
            $table->dateTime('arrive_datetime')->nullable();
            $table->dateTime('checkin_datetime')->nullable();
            $table->dateTime('in_progress_datetime')->nullable();
            $table->dateTime('completed_datetime')->nullable();
            $table->dateTime('checkout_datetime')->nullable();
            $table->text('appointment_notes')->nullable();
            $table->string('reason_visit')->nullable();
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
