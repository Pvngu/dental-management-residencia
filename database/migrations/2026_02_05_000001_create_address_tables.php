<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTables extends Migration
{
    public function addressColumns(Blueprint $table)
    {
        $table->string('address_line_1');
        $table->string('address_line_2')->nullable();
        $table->string('neighborhood')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();
        $table->string('country_code', 3)->nullable();
        $table->string('country_name')->nullable();
        $table->string('reference')->nullable();
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();
        $table->string('contact_name')->nullable();
        $table->string('contact_phone')->nullable();
        $table->text('notes')->nullable();
    }

    public function up()
    {
        // User Addresses Table - includes address_type and is_default
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $this->addressColumns($table);
            $table->enum('address_type', ['home', 'work', 'billing', 'shipping', 'other'])->default('home');
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });

        // Clinic Addresses Table - no address_type or is_default
        Schema::create('clinic_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('clinic_location_id')->unsigned();
            $this->addressColumns($table);
            $table->boolean('status')->default(true);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('clinic_location_id')->references('id')->on('clinic_locations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });

        // Insurance Provider Addresses Table - no address_type or is_default
        Schema::create('insurance_provider_addresses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('insurance_provider_id')->unsigned();
            $this->addressColumns($table);
            $table->boolean('status')->default(true);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->timestamps();

            $table->foreign('insurance_provider_id')->references('id')->on('insurance_providers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurance_provider_addresses');
        Schema::dropIfExists('clinic_addresses');
        Schema::dropIfExists('user_addresses');
    }
}
