<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');
        
        // Core application data
        $this->command->info('📋 Seeding core application data...');
        $this->call(SubscriptionPlansTableSeeder::class);
        $this->call(CompanyTableSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        
        // // Geographic data - Countries
        $this->command->info('🌍 Seeding geographic data...');
        $this->call(CountriesTableSeeder::class);
        
        // User and application settings
        $this->command->info('👥 Seeding users and application settings...');
        $this->call(UsersTableSeeder::class);
        $this->call(ReceptionistSeeder::class);
        $this->call(SettingTableSeeder::class);
        
        // Forms and templates
        // $this->command->info('📝 Seeding forms and templates...');
        // $this->call(FormFieldNamesTableSeeder::class);
        // $this->call(EmailTemplatesTableSeeder::class);
        // $this->call(FormsTableSeeder::class);
        
        // Questionnaire templates
        $this->command->info('📋 Seeding questionnaire templates...');
        $this->call(QuestionnaireTemplateSeeder::class);
        $this->call(QuestionnaireSampleDataSeeder::class);
        
        // Medical catalogs
        $this->command->info('🩺 Seeding medical catalogs...');
        $this->call(DoctorSpecialtySeeder::class);
        $this->call(DoctorDepartmentSeeder::class);
        $this->call(DoctorSeeder::class);
        $this->call(PatientSeeder::class);

        // Patient Messages
        $this->command->info('💬 Seeding patient messages...');
        $this->call(PatientMessageSeeder::class);

        // Insurance Providers and Patient Insurance
        $this->command->info('🏥 Seeding insurance providers and patient insurance data...');
        $this->call(InsuranceProviderSeeder::class);
        $this->call(PatientInsuranceSeeder::class);
        
        // Patient Payment Methods
        $this->command->info('💳 Seeding patient payment methods...');
        $this->call(PatientCreditCardSeeder::class);
        $this->call(PatientBankAccountSeeder::class);
        $this->call(PatientPaypalAccountSeeder::class);
        
        // POS System - Inventory and Sales
        $this->command->info('🏪 Seeding POS system data...');
        $this->call(ItemCategorySeeder::class);
        $this->call(ItemBrandSeeder::class);
        $this->call(ItemManufactureSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(AdjustmentsReasonSeeder::class);
        $this->call(InventoryAdjustmentSeeder::class);
        $this->call(PromotionSeeder::class);
        $this->call(InvoiceSeeder::class);
        // Always seed sales for dashboard data
        $this->call(SaleSeeder::class);

        // Medicines and related data
        $this->command->info('💊 Seeding medicines and related data...');
        $this->call(MedicineSeeder::class);
        $this->call(PurchaseMedicineSeeder::class);
        
        // Facility Management - Rooms and Equipment
        $this->command->info('🏥 Seeding facility management data...');
        $this->call(RoomTypeSeeder::class);
        $this->call(RoomSeeder::class);
        
        // Treatment Types and Appointments
        $this->command->info('📅 Seeding treatment and appointment data...');
        $this->call(TreatmentTypeSeeder::class);
        $this->call(ClinicScheduleSeeder::class);
        $this->call(AppointmentSeeder::class);

        // Open Cases
        $this->command->info('🚨 Seeding open cases...');
        $this->call(OpenCaseSeeder::class);

        // Mail types and Postals
        $this->command->info('✉️ Seeding mail types and postals...');
        $this->call(MailTypeSeeder::class);
        $this->call(PostalSeeder::class);
        $this->call(EmailSeeder::class);

        // Creating SuperAdmin
        $this->command->info('👑 Creating SuperAdmin...');
        \App\SuperAdmin\Classes\SuperAdminCommon::createSuperAdmin(true);

        // Assign Permissions
        $this->command->info('🔑 Assigning Permissions...');
        $this->call(PermissionsSeeder::class);
        
        $this->command->info('✅ Database seeding completed successfully!');
    }
    
    /**
     * Determine if Mexico ZIP codes should be imported.
     * Requires mexican_postal_codes.txt file in seeders directory.
     */
    private function shouldImportMexicoZipCodes(): bool
    {
        $txtFile = database_path('seeders/mexican_postal_codes.txt');
        return file_exists($txtFile);
    }
    
    /**
     * Determine if US ZIP codes should be imported.
     * Requires zip_code_database.csv file in seeders directory.
     */
    private function shouldImportUSZipCodes(): bool
    {
        $csvFile = database_path('seeders/zip_code_database.csv');
        return file_exists($csvFile);
    }
    
    /**
     * Determine if Canada ZIP codes should be imported.
     * Requires canadian_postal_codes.txt file in seeders directory.
     */
    private function shouldImportCanadaZipCodes(): bool
    {
        $txtFile = database_path('seeders/canadian_postal_codes.txt');
        return file_exists($txtFile);
    }
}
