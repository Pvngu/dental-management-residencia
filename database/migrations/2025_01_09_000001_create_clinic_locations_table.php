<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClinicLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('clinic_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->boolean('status')->default(true);
            $table->string('logo')->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->string('twilio_phone_number')->nullable();
            $table->string('twilio_whatsapp_number')->nullable();
            $table->boolean('use_own_twilio')->default(false);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clinic_locations');
    }
}
