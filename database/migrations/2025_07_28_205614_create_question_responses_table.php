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
        Schema::create('question_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->foreign('assignment_id')->references('id')->on('questionnaire_assignments')->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->boolean('value_bool')->nullable();
            $table->decimal('value_numeric', 15, 4)->nullable();
            $table->text('value_text')->nullable();
            $table->text('value_text_clean')->nullable();
            $table->timestamp('response_date')->useCurrent();
            $table->integer('response_time_ms')->nullable();
            $table->boolean('is_modified')->default(false);
            $table->integer('modification_count')->default(0);
            $table->boolean('pii_flag')->default(false);
            $table->string('language_detected', 5)->nullable();
            $table->decimal('sentiment_score', 3, 2)->nullable();
            $table->text('embedding_vector')->nullable();
            $table->json('response_tags')->default('[]');
            $table->integer('version')->default(1);
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            
            // Constraint único
            $table->unique(['assignment_id', 'question_id'], 'ux_qr_assignment_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_responses');
    }
};
