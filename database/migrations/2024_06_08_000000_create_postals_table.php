<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostalsTable extends Migration
{
    public function up()
    {
        Schema::create('postals', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->date('date');
            $table->enum('postal_type', ['received', 'dispatched']);
            $table->string('sender_name')->nullable();
            $table->bigInteger('received_by')->unsigned()->nullable();
            $table->string('file')->nullable();
            $table->bigInteger('patient_id')->unsigned()->nullable();
            $table->bigInteger('mail_type_id')->unsigned()->nullable();
            $table->string('courier_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->enum('status', ['Sent', 'In Transit', 'Received', 'Delivered', 'Pending', 'In Review'])->default('Sent');
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('sent_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('postals');
    }
}
