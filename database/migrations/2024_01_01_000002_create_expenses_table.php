<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('amount');
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->string('expense_for');
            $table->enum('payment_type', ['cash', 'card', 'bank', 'other']);
            $table->string('reference_number')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('expense_categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
