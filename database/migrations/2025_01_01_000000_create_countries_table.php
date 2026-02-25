<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // ISO 3166-1 alpha-3 code
            $table->string('phone_code')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_symbol', 5)->nullable(); // $, €, £, etc.
            $table->string('language', 5)->nullable(); // en, es, fr, etc.
            $table->boolean('status')->default(true);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
