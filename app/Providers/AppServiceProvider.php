<?php

namespace App\Providers;

use App\Models\Form;
use App\Models\Role;
use App\Models\User;
use App\Models\Doctor;
use App\Models\UserAddress;
use App\Models\Company;
use App\Models\Patient;
use App\Models\PatientMessage;
use App\Models\Currency;
use App\Models\Settings;
use App\Models\Appointment;
use App\Models\PatientFile;
use App\Models\PatientNote;
use App\Models\EmailTemplate;
use App\Models\FormFieldName;
use App\Models\AppointmentItem;
use App\Models\OpenCase;
use App\Observers\FormObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use App\Observers\DoctorObserver;
use App\Observers\AddressObserver;
use App\Observers\PatientObserver;
use App\Observers\PatientMessageObserver;
use App\Observers\SettingObserver;
use App\Models\InventoryAdjustment;
use App\Observers\CurrencyObserver;
use App\SuperAdmin\Models\SuperAdmin;
use App\Observers\AppointmentObserver;
use App\Observers\PatientFileObserver;
use App\Observers\PatientNoteObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\EmailTemplateObserver;
use App\Observers\FormFieldNameObserver;
use App\Observers\AppointmentItemObserver;
use App\Observers\OpenCaseObserver;
use App\SuperAdmin\Observers\CompanyObserver;
use App\Observers\InventoryAdjustmentObserver;
use App\SuperAdmin\Observers\SuperAdminObserver;
use App\Services\ClinicContext;

class AppServiceProvider extends ServiceProvider
{

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    protected $superAdminNamespace = 'App\\SuperAdmin\\Http\\Controllers';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClinicContext::class, function ($app) {
            return new ClinicContext();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Doctor::observe(DoctorObserver::class);
        Patient::observe(PatientObserver::class);
        PatientMessage::observe(PatientMessageObserver::class);
        Settings::observe(SettingObserver::class);
        Currency::observe(CurrencyObserver::class);
        Role::observe(RoleObserver::class);
        EmailTemplate::observe(EmailTemplateObserver::class);
        Form::observe(FormObserver::class);
        InventoryAdjustment::observe(InventoryAdjustmentObserver::class);
        FormFieldName::observe(FormFieldNameObserver::class);
        Company::observe(CompanyObserver::class);
        SuperAdmin::observe(SuperAdminObserver::class);
        
        // Patient History Observers
        Appointment::observe(AppointmentObserver::class);
        AppointmentItem::observe(AppointmentItemObserver::class);
        PatientNote::observe(PatientNoteObserver::class);
        UserAddress::observe(AddressObserver::class);
        PatientFile::observe(PatientFileObserver::class);
        OpenCase::observe(OpenCaseObserver::class);
    }
}
