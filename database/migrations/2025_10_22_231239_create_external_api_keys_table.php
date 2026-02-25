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
        Schema::create('external_api_keys', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->string('name')->comment('Nombre del cliente/CRM (ej: "CRM Principal", "Sistema Contabilidad")');
            $table->string('api_key', 64)->unique()->comment('Token de API generado');
            $table->text('description')->nullable()->comment('Descripción o notas adicionales');
            $table->string('contact_email')->nullable()->comment('Email de contacto del cliente');
            $table->boolean('is_active')->default(true)->comment('Estado del API key');
            $table->timestamp('last_used_at')->nullable()->comment('Última vez que se usó este token');
            $table->timestamp('expires_at')->nullable()->comment('Fecha de expiración (opcional)');
            $table->json('allowed_ips')->nullable()->comment('IPs permitidas (opcional, array JSON)');
            $table->json('allowed_domains')->nullable()->comment('Dominios permitidos para CORS (opcional, array JSON)');
            $table->integer('request_count')->default(0)->comment('Contador de requests realizados');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->index('api_key');
            $table->index('is_active');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_api_keys');
    }
};
