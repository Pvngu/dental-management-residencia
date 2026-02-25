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
        Schema::create('patient_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('sent_by_user_id')->unsigned()->nullable();
            $table->foreign('sent_by_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->text('message');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'received'])->default('pending');
            $table->string('phone_number');
            $table->enum('channel', ['sms', 'whatsapp'])->default('sms');
            $table->string('external_message_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['patient_id', 'created_at']);
            $table->index(['patient_id', 'read_at']);
            $table->index('direction');
            $table->index('status');
            $table->index('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_messages');
    }
};
