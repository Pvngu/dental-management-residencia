<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Skip if table already exists
        if (Schema::hasTable('company_landing_pages')) {
            return;
        }
        
        // Create company_landing_pages table
        Schema::create('company_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            
            $table->string('template_id')->default('dental-classic');
            $table->string('custom_domain')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#f59e0b');
            
            // page section
            $table->string('page_title')->default('Tu Sonrisa es Nuestra Prioridad');
            $table->text('page_subtitle')->default('Ofrecemos atención dental de calidad con tecnología de vanguardia y un equipo profesional comprometido con tu salud bucal.');
            $table->string('page_image_url')->nullable();
            
            // About section
            $table->string('about_title')->default('Acerca de Nosotros');
            $table->text('about_text')->default('Somos una clínica dental moderna comprometida con brindar el mejor cuidado dental usando tecnología avanzada y técnicas innovadoras.');
            $table->string('about_image_url')->nullable();
            
            // Services section
            $table->boolean('services_enabled')->default(true);
            $table->string('services_title')->default('Nuestros Servicios');
            $table->text('services_subtitle')->default('Ofrecemos una amplia gama de servicios dentales para todas las edades');
            
            // Team section
            $table->boolean('team_enabled')->default(true);
            $table->string('team_title')->default('Nuestro Equipo');
            $table->text('team_subtitle')->default('Profesionales altamente calificados a tu servicio');
            
            // JSON fields
            $table->json('contact_info')->nullable();
            $table->json('social_media')->nullable();
            $table->json('seo_meta')->nullable();
            $table->json('custom_sections')->nullable();
            
            // Custom styling
            $table->text('custom_css')->nullable();
            
            // Publishing settings
            $table->boolean('is_published')->default(false);
            $table->boolean('show_online_booking')->default(true);
            $table->boolean('show_phone_booking')->default(true);

            // enabled features
            $table->boolean('isactive_landing_page')->default(true);
            
            $table->timestamps();
        });

        // Add indexes for performance
        Schema::table('company_landing_pages', function (Blueprint $table) {
            $table->index('company_id');
            $table->index('template_id');
            $table->index('custom_domain');
        }); 
        
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {

        Schema::dropIfExists('company_landing_pages');
    }
};
