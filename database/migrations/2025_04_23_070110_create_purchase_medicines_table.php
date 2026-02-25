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
        Schema::create('purchase_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('payment_type')->default(null);
            $table->string('payment_status')->default(null);
            $table->decimal('discount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('adjustments', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_medicines');
    }
};
