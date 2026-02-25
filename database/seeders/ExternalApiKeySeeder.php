<?php

namespace Database\Seeders;

use App\Models\ExternalApiKey;
use Illuminate\Database\Seeder;

class ExternalApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea ejemplos de diferentes tipos de clientes externos
     * para demostrar las capacidades del sistema de múltiples API keys.
     */
    public function run(): void
    {
        // NOTA: Asegúrate de tener al menos una company en tu base de datos
        // o comenta company_id en los ejemplos
        
        echo "\n🔑 Creando tokens de ejemplo...\n\n";

        // 1. Cliente Principal - Sin restricciones (requiere company_id)
        $crmPrincipal = ExternalApiKey::withoutGlobalScope(\App\Scopes\CompanyScope::class)->create([
            'company_id' => 1, // Ajusta según tu base de datos
            'name' => 'CRM Principal',
            'api_key' => ExternalApiKey::generateKey(),
            'description' => 'Sistema CRM principal de la empresa. Acceso completo sin restricciones.',
            'contact_email' => 'soporte@crm-principal.com',
            'is_active' => true,
        ]);
        
        echo "✅ CRM Principal creado\n";
        echo "   Token: {$crmPrincipal->api_key}\n";
        echo "   Company ID: {$crmPrincipal->company_id}\n";
        echo "   Sin restricciones de IP ni expiración\n\n";

        // 2. Sistema Contabilidad - Con restricción de IP y dominio
        $contabilidad = ExternalApiKey::withoutGlobalScope(\App\Scopes\CompanyScope::class)->create([
            'company_id' => 1,
            'name' => 'Sistema Contabilidad',
            'api_key' => ExternalApiKey::generateKey(),
            'description' => 'Integración con sistema contable. Solo accesible desde servidor y dominio específicos.',
            'contact_email' => 'it@contabilidad-empresa.com',
            'is_active' => true,
            'allowed_ips' => [
                '192.168.1.100',  // IP del servidor de contabilidad
                '10.0.0.50',      // IP de respaldo
            ],
            'allowed_domains' => [
                'contabilidad.empresa.com',
                'app.contabilidad.com',
            ],
        ]);
        
        echo "✅ Sistema Contabilidad creado\n";
        echo "   Token: {$contabilidad->api_key}\n";
        echo "   Company ID: {$contabilidad->company_id}\n";
        echo "   IPs permitidas: " . implode(', ', $contabilidad->allowed_ips) . "\n";
        echo "   Dominios permitidos: " . implode(', ', $contabilidad->allowed_domains) . "\n\n";

        // 3. Partner Temporal - Con fecha de expiración y dominio
        $partnerTemporal = ExternalApiKey::withoutGlobalScope(\App\Scopes\CompanyScope::class)->create([
            'company_id' => 1,
            'name' => 'Partner Temporal',
            'api_key' => ExternalApiKey::generateKey(),
            'description' => 'Acceso temporal para partner externo. Expira en 3 meses.',
            'contact_email' => 'api@partner-xyz.com',
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
            'allowed_domains' => [
                'partner-xyz.com',
                'app.partner-xyz.com',
            ],
        ]);
        
        echo "✅ Partner Temporal creado\n";
        echo "   Token: {$partnerTemporal->api_key}\n";
        echo "   Company ID: {$partnerTemporal->company_id}\n";
        echo "   Expira: {$partnerTemporal->expires_at}\n";
        echo "   Dominios permitidos: " . implode(', ', $partnerTemporal->allowed_domains) . "\n\n";

        // 4. CRM Ventas - Activo, sin restricciones
        $crmVentas = ExternalApiKey::withoutGlobalScope(\App\Scopes\CompanyScope::class)->create([
            'company_id' => 1,
            'name' => 'CRM Ventas',
            'api_key' => ExternalApiKey::generateKey(),
            'description' => 'Sistema CRM específico para el equipo de ventas.',
            'contact_email' => 'ventas@empresa.com',
            'is_active' => true,
        ]);
        
        echo "✅ CRM Ventas creado\n";
        echo "   Token: {$crmVentas->api_key}\n";
        echo "   Company ID: {$crmVentas->company_id}\n\n";

        // 5. Sistema Desarrollo - Inactivo (ejemplo de token deshabilitado)
        $desarrollo = ExternalApiKey::withoutGlobalScope(\App\Scopes\CompanyScope::class)->create([
            'company_id' => 1,
            'name' => 'Sistema Desarrollo',
            'api_key' => ExternalApiKey::generateKey(),
            'description' => 'Token para ambiente de desarrollo. Actualmente desactivado.',
            'contact_email' => 'dev@empresa.com',
            'is_active' => false, // INACTIVO
        ]);
        
        echo "⚠️  Sistema Desarrollo creado (INACTIVO)\n";
        echo "   Token: {$desarrollo->api_key}\n";
        echo "   Company ID: {$desarrollo->company_id}\n";
        echo "   Estado: Desactivado para pruebas\n\n";

        // Simular uso para algunos tokens (opcional)
        $crmPrincipal->markAsUsed();
        $crmPrincipal->markAsUsed();
        $crmPrincipal->markAsUsed();
        
        $contabilidad->markAsUsed();

        echo "\n📊 Resumen:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Total de tokens creados: 5\n";
        echo "Tokens activos: 4\n";
        echo "Tokens inactivos: 1\n";
        echo "Tokens con restricción IP: 1 (Sistema Contabilidad)\n";
        echo "Tokens con restricción de dominio: 2 (Contabilidad, Partner)\n";
        echo "Tokens con expiración: 1 (Partner Temporal)\n";
        echo "Todos asociados a company_id: 1\n";
        echo "\n";
        echo "⚠️  IMPORTANTE: Guarda estos tokens en un lugar seguro.\n";
        echo "    No podrás verlos completos nuevamente después de esta ejecución.\n";
        echo "    Si pierdes un token, usa el endpoint 'regenerate' para crear uno nuevo.\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // Instrucciones de prueba
        echo "🧪 Para probar los tokens:\n";
        echo "\n";
        echo "# 1. Probar CRM Principal (debe funcionar):\n";
        echo "curl -X GET \"http://localhost/api/external/patients/by-phone/1234567890\" \\\n";
        echo "  -H \"Authorization: Bearer {$crmPrincipal->api_key}\"\n";
        echo "\n";
        echo "# 2. Probar Sistema Contabilidad (puede fallar si tu IP no está permitida):\n";
        echo "curl -X GET \"http://localhost/api/external/patients/by-phone/1234567890\" \\\n";
        echo "  -H \"Authorization: Bearer {$contabilidad->api_key}\"\n";
        echo "\n";
        echo "# 3. Probar Sistema Desarrollo (debe fallar - está inactivo):\n";
        echo "curl -X GET \"http://localhost/api/external/patients/by-phone/1234567890\" \\\n";
        echo "  -H \"Authorization: Bearer {$desarrollo->api_key}\"\n";
        echo "\n";
        echo "# 4. Ver estadísticas:\n";
        echo "curl -X GET \"http://localhost/api/external-api-keys/statistics\" \\\n";
        echo "  -H \"Authorization: Bearer YOUR_ADMIN_TOKEN\"\n";
        echo "\n";
    }
}
