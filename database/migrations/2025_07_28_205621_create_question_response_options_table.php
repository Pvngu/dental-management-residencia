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
        Schema::create('question_response_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('response_id');
            $table->foreign('response_id')->references('id')->on('question_responses')->onDelete('cascade');
            $table->unsignedBigInteger('option_id');
            $table->foreign('option_id')->references('id')->on('question_options')->onDelete('cascade');
            $table->string('option_code', 50)->nullable();
            $table->decimal('value_numeric', 15, 4)->nullable();
            $table->json('response_tags')->default('[]');
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            
            // Constraint único
            $table->unique(['response_id', 'option_id'], 'ux_qro_response_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_response_options');
    }
};
