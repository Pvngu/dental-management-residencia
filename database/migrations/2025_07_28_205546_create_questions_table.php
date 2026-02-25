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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')->references('id')->on('questionnaire_sections')->onDelete('cascade');
            $table->string('code', 50);
            $table->text('prompt');
            $table->string('response_type', 20); // TEXT, BOOL, SINGLE_CHOICE, MULTI_CHOICE, INFO, DATE, NUMERIC, FILE
            $table->integer('position')->default(1);
            $table->boolean('is_required')->default(true);
            $table->decimal('weight', 5, 2)->nullable();
            $table->json('skip_logic')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('questions');
    }
};
