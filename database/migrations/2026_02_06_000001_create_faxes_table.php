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
        Schema::create('faxes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            
            $table->bigInteger('patient_id')->unsigned()->nullable()->default(null);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null')->onUpdate('cascade');
            
            $table->bigInteger('insurance_provider_id')->unsigned()->nullable()->default(null);
            $table->foreign('insurance_provider_id')->references('id')->on('insurance_providers')->onDelete('set null')->onUpdate('cascade');
            
            $table->bigInteger('created_by')->unsigned()->nullable()->default(null);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            
            $table->string('to_number');
            $table->string('from_number')->nullable();
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('status', ['queued', 'sending', 'sent', 'failed', 'received'])->default('queued');
            $table->string('file')->nullable();
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('transmitted_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faxes');
    }
};
