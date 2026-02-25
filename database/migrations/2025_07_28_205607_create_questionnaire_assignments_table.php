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
        Schema::create('questionnaire_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->foreign('instance_id')->references('id')->on('questionnaire_instances')->onDelete('cascade');
            $table->integer('employee_id')->nullable(); // null = anónimo
            $table->string('status', 20)->default('IN_PROGRESS');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->string('public_folio', 50)->nullable();
            $table->string('public_token', 100)->nullable();
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
        Schema::dropIfExists('questionnaire_assignments');
    }
};
