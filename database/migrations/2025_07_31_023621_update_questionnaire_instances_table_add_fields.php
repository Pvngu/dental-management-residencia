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
        Schema::table('questionnaire_instances', function (Blueprint $table) {
            // Add notes field
            if (!Schema::hasColumn('questionnaire_instances', 'notes')) {
                $table->text('notes')->nullable()->after('anonymize_responses');
            }
            
            // Add created_by if not exists
            if (!Schema::hasColumn('questionnaire_instances', 'created_by')) {
                $table->bigInteger('created_by')->unsigned()->nullable()->after('anonymize_responses');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_instances', function (Blueprint $table) {
            $table->dropColumn(['notes']);
            
            if (Schema::hasColumn('questionnaire_instances', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
