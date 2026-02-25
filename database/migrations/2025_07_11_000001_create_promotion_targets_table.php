<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionTargetsTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_targets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('promotion_id')->unsigned()->nullable();
            $table->enum('target_type', ['product', 'brand', 'category']);
            $table->bigInteger('target_id')->unsigned()->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_targets');
    }
}
