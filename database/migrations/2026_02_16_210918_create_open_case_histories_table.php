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
        Schema::create('open_case_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('open_case_id')->unsigned();
            $table->foreign('open_case_id')->references('id')->on('open_cases')->onDelete('cascade')->onUpdate('cascade');
            $table->string('action'); // created, updated, status_changed, priority_changed, resolved, reopened
            $table->string('field_name')->nullable(); // Field that changed (if applicable)
            $table->text('old_value')->nullable(); // Old value before change
            $table->text('new_value')->nullable(); // New value after change
            $table->bigInteger('user_id')->unsigned()->nullable(); // Who made the change
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('open_case_histories');
    }
};
