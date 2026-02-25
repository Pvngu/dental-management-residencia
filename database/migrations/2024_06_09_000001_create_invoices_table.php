<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned()->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->date('payment_due_on')->nullable();
            $table->string('status')->nullable();
            $table->string('company_name')->nullable();
            $table->string('person_name')->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('total_payable', 15, 2)->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
