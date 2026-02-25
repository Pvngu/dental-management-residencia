<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_medicines', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_medicine_id')->unsigned();
            $table->foreign('purchase_medicine_id')->references('id')->on('purchase_medicines')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('medicine_id')->unsigned();
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade')->onUpdate('cascade');
            $table->string('lot_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            
            // Ensure lot_no is unique per medicine
            $table->unique(['medicine_id', 'lot_no'], 'unique_medicine_lot_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_medicines');
    }
};
