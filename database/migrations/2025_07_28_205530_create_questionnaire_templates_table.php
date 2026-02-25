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
        Schema::create('questionnaire_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('version', 20);
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_evergreen')->default(false);
            $table->string('normative_ref', 100)->nullable();
            $table->string('target_population', 10)->default('ALL');
            $table->integer('estimated_duration')->nullable();
            $table->json('config_json')->nullable();
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('questionnaire_templates');
    }
};
