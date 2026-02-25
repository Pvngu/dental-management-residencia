<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('patient_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type', 100);
            $table->nullableMorphs('referenceable');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(1);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_histories');
    }
}
