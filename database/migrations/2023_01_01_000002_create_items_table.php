<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->enum('unit', ['box','pcs','dozen','kg','g','mg','lb','oz','m','cm','mm','in','ft','km','ml'])->nullable();
            $table->text('description')->nullable();
            $table->integer('available_quantity')->default(0);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->string('sku')->nullable();
            $table->boolean('returnable')->default(false);
            $table->enum('type', ['service', 'goods', 'medicine'])->default('goods');
            //dimensions
            $table->float('item_length')->nullable();
            $table->float('item_width')->nullable();
            $table->float('item_height')->nullable();
            $table->string('dimension_unit')->nullable();
            
            $table->float('weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->integer('alert_quantity')->default(5);
            $table->bigInteger('manufacturer_id')->unsigned()->nullable();
            $table->bigInteger('brand_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('item_categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('manufacturer_id')->references('id')->on('item_manufactures')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('brand_id')->references('id')->on('item_brands')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();

            $table->index('sku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}
