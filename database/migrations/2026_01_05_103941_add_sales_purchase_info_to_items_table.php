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
        // First create suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Then add columns to items table
        Schema::table('items', function (Blueprint $table) {
            // Sales Information
            $table->boolean('is_sellable')->default(true)->after('brand_id');
            $table->decimal('sale_price', 10, 2)->nullable()->after('is_sellable');
            $table->text('sale_description')->nullable()->after('sale_price');
            
            // Purchase Information
            $table->boolean('is_purchasable')->default(true)->after('sale_description');
            $table->decimal('cost_price', 10, 2)->nullable()->after('is_purchasable');
            $table->text('purchase_description')->nullable()->after('cost_price');
            $table->bigInteger('supplier_id')->unsigned()->nullable()->after('purchase_description');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'is_sellable',
                'sale_price',
                'sale_description',
                'is_purchasable',
                'cost_price',
                'purchase_description',
                'supplier_id'
            ]);
        });

        Schema::dropIfExists('suppliers');
    }
};
