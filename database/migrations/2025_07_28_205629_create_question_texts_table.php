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
        Schema::create('question_texts', function (Blueprint $table) {
            $table->unsignedBigInteger('response_id')->primary();
            $table->foreign('response_id')->references('id')->on('question_responses')->onDelete('cascade');
            $table->text('raw_text');
            $table->text('clean_text')->nullable();
            $table->string('lang', 5)->nullable();
            $table->decimal('sentiment', 4, 3)->nullable();
            $table->decimal('toxicity', 4, 3)->nullable();
            $table->text('keywords')->nullable();
            $table->json('entities_json')->nullable();
            $table->text('embedding_vector')->nullable();
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
        Schema::dropIfExists('question_texts');
    }
};
