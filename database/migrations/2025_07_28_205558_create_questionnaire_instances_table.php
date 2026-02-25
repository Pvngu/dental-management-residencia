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
        Schema::create('questionnaire_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->foreign('template_id')->references('id')->on('questionnaire_templates')->onDelete('cascade');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->timestamp('launch_date')->useCurrent();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('population_mode', 30)->default('MANUAL_SELECTION');
            $table->string('launch_reason', 20)->default('PERIODIC');
            $table->string('status', 20)->default('DRAFT');
            $table->json('target_sucursales')->nullable();
            $table->json('target_roles')->nullable();
            $table->json('config_overrides')->nullable();
            $table->boolean('anonymize_responses')->default(false);
            $table->integer('created_by');
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
        Schema::dropIfExists('questionnaire_instances');
    }
};
