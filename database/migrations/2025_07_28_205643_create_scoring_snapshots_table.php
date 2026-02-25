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
        Schema::create('scoring_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->foreign('assignment_id')->references('id')->on('questionnaire_assignments')->onDelete('cascade');
            $table->unsignedBigInteger('formula_id');
            $table->foreign('formula_id')->references('id')->on('scoring_formulas')->onDelete('cascade');
            $table->decimal('raw_score', 10, 4)->nullable();
            $table->decimal('normalized_score', 10, 4)->nullable();
            $table->string('result_label', 100)->nullable();
            $table->string('result_category', 50)->nullable();
            $table->boolean('requires_clinical_review')->default(false);
            $table->boolean('requires_follow_up')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->json('response_tags')->default('[]');
            $table->timestamp('computed_at')->useCurrent();
            $table->json('inputs_json')->nullable();
            $table->json('flags_json')->nullable();
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
        Schema::dropIfExists('scoring_snapshots');
    }
};
