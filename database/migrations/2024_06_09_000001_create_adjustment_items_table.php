<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentItemsTable extends Migration
{
    public function up()
    {
        Schema::create('adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->bigInteger('adjustment_id')->unsigned();
            $table->bigInteger('item_id')->unsigned();
            $table->integer('quantity_adjusted')->default(0);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('adjustment_id')->references('id')->on('inventory_adjustments')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_items');
    }
}
