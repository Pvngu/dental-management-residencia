<?php

use Examyou\RestAPI\Facades\ApiRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Landing\LandingController;

// Public Landing Page Routes (No Auth Required)
Route::prefix('landing')->group(function () {
    Route::get('/{companySlug}', [LandingController::class, 'show'])
        ->name('landing.show');
});

// Test route for error page (remove in production)
Route::get('/test/error-page', function () {
    return response()->view('errors.company-not-found', [
        'companySlug' => 'clinica-inexistente',
        'reason' => 'not_found'
    ], 404);
});

// Test route for disabled landing page (remove in production)
Route::get('/test/error-page-disabled', function () {
    return response()->view('errors.company-not-found', [
        'companySlug' => 'test-clinic',
        'reason' => 'disabled',
        'companyName' => 'Clínica Dental Test'
    ], 403);
});

// API routes for landing page (No Auth Required)
Route::prefix('api/landing')->group(function () {
    Route::get('/company/{companySlug}', [LandingController::class, 'getCompanyData']);
    Route::get('/{companyId}/services', [LandingController::class, 'getServices']);
    Route::get('/{companyId}/doctors', [LandingController::class, 'getDoctors']);
    Route::post('/contact', [LandingController::class, 'submitContact']);
    Route::post('/appointment', [LandingController::class, 'bookAppointment']);
});

// External API routes (Token Authentication Required)
Route::prefix('api/external')->middleware(['external.api.token'])->group(function () {
    Route::get('/patients/by-phone/{phone}', [App\Http\Controllers\Api\ExternalApiController::class, 'getPatientByPhone'])
        ->name('api.external.patients.by-phone');
});

// Admin Routes
ApiRoute::group(['namespace' => 'App\Http\Controllers\Api'], function () {
    ApiRoute::get('all-langs', ['as' => 'api.extra.all-langs', 'uses' => 'AuthController@allEnabledLangs']);
    ApiRoute::get('all-users', ['as' => 'api.extra.all-users', 'uses' => 'AuthController@allUsers']);

    // Test routes without authentication (for testing purposes)
    ApiRoute::get('test/countries', ['as' => 'api.test.countries', 'uses' => 'CountryController@allOptions']);
    ApiRoute::get('test/states', ['as' => 'api.test.states', 'uses' => 'StateController@allOptions']);
    ApiRoute::get('test/zip-codes', ['as' => 'api.test.zip-codes', 'uses' => 'ZipCodeController@allOptions']);
    ApiRoute::get('test/countries/statistics', ['as' => 'api.test.countries.statistics', 'uses' => 'CountryController@statistics']);
    ApiRoute::get('test/doctor-specialty/all', ['as' => 'api.test.doctor-specialties', 'uses' => 'DoctorSpecialtyController@getAllSpecialties']);
    ApiRoute::get('test/doctor-specialty/show/{xid}', ['as' => 'api.test.doctor-specialties.show', 'uses' => 'DoctorSpecialtyController@show']);

    // Check visibility of module according to subscription plan
    ApiRoute::post('check-subscription-module-visibility', ['as' => 'api.extra.check-subscription-module-visibility', 'uses' => 'AuthController@checkSubscriptionModuleVisibility']);
    ApiRoute::post('visible-subscription-modules', ['as' => 'api.extra.visible-subscription-modules', 'uses' => 'AuthController@visibleSubscriptionModules']);

    // Broadcasting authentication route (outside middleware to handle JWT manually)
    ApiRoute::post('broadcasting/auth', ['as' => 'api.broadcasting.auth', 'uses' => function () {
        try {
            // Try to authenticate using the 'api' guard for JWT tokens
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // Set the authenticated user for Laravel's Broadcast system
            auth()->setUser($user);
            
            // Process the channel authorization
            return Broadcast::auth(request());
        } catch (\Exception $e) {
            \Log::error('Broadcasting auth error: ' . $e->getMessage());
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        }
    }]);

    ApiRoute::group(['middleware' => ['api.auth.check']], function () {
        ApiRoute::post('dashboard', ['as' => 'api.extra.dashboard', 'uses' => 'AuthController@dashboard']);
        ApiRoute::post('upload-file', ['as' => 'api.extra.upload-file', 'uses' => 'AuthController@uploadFile']);
        ApiRoute::post('download-file', ['as' => 'api.extra.download-file', 'uses' => 'AuthController@downloadFile']);
        ApiRoute::post('profile', ['as' => 'api.extra.profile', 'uses' => 'AuthController@profile']);
        ApiRoute::post('preferences', ['as' => 'api.extra.preferences', 'uses' => 'AuthController@preferences']);
        ApiRoute::post('user', ['as' => 'api.extra.user', 'uses' => 'AuthController@user']);
        ApiRoute::get('timezones', ['as' => 'api.extra.user', 'uses' => 'AuthController@getAllTimezones']);
    });

    // Routes Accessable to thouse user who have permissions realted to route
    ApiRoute::group(['middleware' => ['api.permission.check', 'api.auth.check', 'license-expire', 'set.clinic']], function () {
        $options = [
        ];

        // External API Keys Management (Admin only)
        ApiRoute::get('external-api-keys', ['as' => 'api.external-api-keys.index', 'uses' => 'ExternalApiKeyController@index']);
        ApiRoute::post('external-api-keys', ['as' => 'api.external-api-keys.store', 'uses' => 'ExternalApiKeyController@store']);
        ApiRoute::put('external-api-keys/{id}', ['as' => 'api.external-api-keys.update', 'uses' => 'ExternalApiKeyController@update']);
        ApiRoute::delete('external-api-keys/{id}', ['as' => 'api.external-api-keys.destroy', 'uses' => 'ExternalApiKeyController@destroy']);
        ApiRoute::post('external-api-keys/{id}/regenerate', ['as' => 'api.external-api-keys.regenerate', 'uses' => 'ExternalApiKeyController@regenerate']);
        ApiRoute::post('external-api-keys/{id}/toggle-status', ['as' => 'api.external-api-keys.toggle-status', 'uses' => 'ExternalApiKeyController@toggleStatus']);
        ApiRoute::get('external-api-keys/statistics', ['as' => 'api.external-api-keys.statistics', 'uses' => 'ExternalApiKeyController@statistics']);

        // Address Set Default
        ApiRoute::post('addresses/set-default/{xid}', ['as' => 'api.addresses.set-default', 'uses' => 'AddressController@setDefault']);
        
        // Clinic Locations - Custom endpoint for management interface  
        ApiRoute::get('clinic-locations-management', ['as' => 'api.clinic-locations.management', 'uses' => 'ClinicLocationController@indexWithLimits']);
        ApiRoute::resource('clinic-locations', 'ClinicLocationController', $options);

        // Imports
        ApiRoute::post('users/import', ['as' => 'api.users.import', 'uses' => 'UsersController@import']);

        // Item Inventory Adjustment History
        ApiRoute::get('inventory-adjustments/item/{xid}', ['as' => 'api.inventory-adjustments.item-history', 'uses' => 'InventoryAdjustmentController@getItemAdjustmentHistory']);

        // Update Status
        ApiRoute::post('email-templates/update-status', ['as' => 'api.email-templates.update-status', 'uses' => 'EmailTemplateController@updateStatus']);
        ApiRoute::post('forms/update-status', ['as' => 'api.forms.update-status', 'uses' => 'FormController@updateStatus']);
        ApiRoute::post('form-field-names/update-status', ['as' => 'api.form-field-name.update-status', 'uses' => 'FormFieldNameController@updateStatus']);

        // All Forms
        ApiRoute::get('forms/all', ['as' => 'api.forms.all', 'uses' => 'FormController@allForms']);
        ApiRoute::resource('forms', 'FormController', $options);

        ApiRoute::get('email-templates/all', ['as' => 'api.email-templates.all', 'uses' => 'EmailTemplateController@allEmailTemplates']);
        ApiRoute::resource('email-templates', 'EmailTemplateController', $options);

        ApiRoute::get('form-field-names/all', ['as' => 'api.form-field-names.all', 'uses' => 'FormFieldNameController@allFormFieldNames']);
        ApiRoute::resource('form-field-names', 'FormFieldNameController', $options);

        ApiRoute::resource('promotions', 'PromotionController', $options);
        ApiRoute::resource('promotion_targets', 'PromotionTargetController', $options);

        // Notifications
        ApiRoute::get('notifications/unread-count', ['as' => 'api.notifications.unread-count', 'uses' => 'NotificationController@unreadCount']);
        ApiRoute::post('notifications/{xid}/read', ['as' => 'api.notifications.mark-as-read', 'uses' => 'NotificationController@markAsRead']);
        ApiRoute::post('notifications/mark-all-read', ['as' => 'api.notifications.mark-all-read', 'uses' => 'NotificationController@markAllAsRead']);
        ApiRoute::resource('notifications', 'NotificationController', ['as' => 'api', 'only' => ['index']]);

        // Create Menu Update
        ApiRoute::post('companies/update-create-menu', ['as' => 'api.companies.update-create-menu', 'uses' => 'CompanyController@updateCreateMenu']);

        // Landing Page Settings
        ApiRoute::get('companies/current-settings', ['as' => 'api.companies.current-settings', 'uses' => 'LandingPageController@getCurrentSettings']);
        ApiRoute::get('companies/landing-page-data', ['as' => 'api.companies.landing-page-data', 'uses' => 'LandingPageController@getLandingPageData']);
        ApiRoute::post('companies/update-landing-page', ['as' => 'api.companies.update-landing-page', 'uses' => 'LandingPageController@updateSettings']);
        
        // Company Landing Pages CRUD
        ApiRoute::resource('company-landing-pages', 'CompanyLandingPageController', $options);

        ApiRoute::resource('company-landing-pages', 'CompanyLandingPageController', $options);

        ApiRoute::get('users/{user}/clinic-matrix', ['as' => 'api.users.clinic-matrix', 'uses' => 'UsersController@clinicMatrix']);
        ApiRoute::post('users/{user}/update-clinics', ['as' => 'api.users.update-clinics', 'uses' => 'UsersController@updateClinics']);
        ApiRoute::resource('users', 'UsersController', $options);
        ApiRoute::resource('user-statuses', 'UserStatusController', $options);
        ApiRoute::resource('companies', 'CompanyController', ['as' => 'api', 'only' => ['update']]);
        ApiRoute::resource('permissions', 'PermissionController', ['as' => 'api', 'only' => ['index']]);
        ApiRoute::resource('roles', 'RolesController', $options);

        ApiRoute::get('doctor-departments/all', ['as' => 'api.doctor-departments.all', 'uses' => 'DoctorDepartmentController@allOptions']);
        ApiRoute::resource('doctor-departments', 'DoctorDepartmentController', $options);
        
        // Doctor custom store and update endpoints
        ApiRoute::get('doctors/available', ['as' => 'api.doctors.available', 'uses' => 'DoctorController@available']);
        ApiRoute::post('doctors/availability', ['as' => 'api.doctors.availability', 'uses' => 'DoctorController@availibleDoctors']);
        ApiRoute::get('doctors/stats', ['as' => 'api.doctors.stats', 'uses' => 'DoctorController@getStats']);
        ApiRoute::get('doctors/status', ['as' => 'api.doctors.status', 'uses' => 'DoctorController@getStatus']);
        ApiRoute::get('doctors/{xid}/info', ['as' => 'api.doctors.info', 'uses' => 'DoctorController@getDoctorInfo']);
        ApiRoute::post('doctors/store', ['as' => 'api.doctors.store_doctor', 'uses' => 'DoctorController@storeDoctor']);
        ApiRoute::post('doctors/status', ['as' => 'api.doctors.update_status', 'uses' => 'DoctorController@updateStatus']);
        ApiRoute::post('doctors/{xid}/status', ['as' => 'api.doctors.update_doctor_status', 'uses' => 'DoctorController@updateDoctorStatus']);
        ApiRoute::put('doctors/{xid}/update', ['as' => 'api.doctors.update_doctor', 'uses' => 'DoctorController@updateDoctor']);
        ApiRoute::put('doctors/{xid}/addresses', ['as' => 'api.doctors.update_addresses', 'uses' => 'DoctorController@updateAddresses']);
        ApiRoute::resource('doctors', 'DoctorController', $options);

        // Sellers
        ApiRoute::resource('sellers', 'SellerController', $options);

        ApiRoute::get('doctor-schedules/get-all-schedules', ['as' => 'api.doctor-schedules.get-all-schedules', 'uses' => 'DoctorScheduleController@getAllSchedules']);
        ApiRoute::post('doctor-schedules/store', ['as' => 'api.doctor-schedules.store-schedule', 'uses' => 'DoctorScheduleController@storeSchedule']);
        ApiRoute::put('doctor-schedules/{xid}/update', ['as' => 'api.doctor-schedules.update-schedule', 'uses' => 'DoctorScheduleController@updateSchedule']);
        ApiRoute::resource('doctor-schedules', 'DoctorScheduleController', $options);

        // Doctor Schedule Days endpoints
        ApiRoute::post('doctor-schedule-days/create-appointment', ['as' => 'api.doctor-schedule-days.create-appointment', 'uses' => 'DoctorScheduleDayController@createAppointment']);
        ApiRoute::put('doctor-schedule-days/update-appointment', ['as' => 'api.doctor-schedule-days.update-appointment', 'uses' => 'DoctorScheduleDayController@updateAppointment']);

        ApiRoute::get('doctor-schedule-days/available-slots', ['as' => 'api.doctor-schedule-days.available-slots', 'uses' => 'DoctorScheduleDayController@getAvailableSlots']);
        ApiRoute::get('doctor-schedule-days/monthly-available-slots', ['as' => 'api.doctor-schedule-days.monthly-available-slots', 'uses' => 'DoctorScheduleDayController@getMonthlyAvailableSlots']);
        ApiRoute::resource('doctor-schedule-days', 'DoctorScheduleDayController', $options);

        // Attendance routes
        ApiRoute::post('attendance/toggle', ['as' => 'api.attendances.toggle', 'uses' => 'AttendanceController@toggle']);
        ApiRoute::get('attendance/summary', ['as' => 'api.attendances.summary', 'uses' => 'AttendanceController@summary']);
        ApiRoute::get('attendance/history', ['as' => 'api.attendances.history', 'uses' => 'AttendanceController@history']);
        ApiRoute::resource('attendances', 'AttendanceController', $options);

        // Patient custom store and update endpoints
        ApiRoute::post('patients/store', ['as' => 'api.patients.store_patient', 'uses' => 'PatientController@storePatient']);
        ApiRoute::put('patients/{xid}/update', ['as' => 'api.patients.update_patient', 'uses' => 'PatientController@updatePatient']);
        ApiRoute::put('patients/{xid}/addresses', ['as' => 'api.patients.update_addresses', 'uses' => 'PatientController@updateAddresses']);
        ApiRoute::get('patients/{id}/overview', ['as' => 'api.patients.overview', 'uses' => 'PatientController@overview']);
        // Patient dental chart endpoints (store only the conditions object)
        ApiRoute::get('patients/{id}/dental-chart', ['as' => 'api.patients.dental-chart.show', 'uses' => 'PatientController@dentalChart']);
        ApiRoute::put('patients/{xid}/dental-chart', ['as' => 'api.patients.dental-chart.update', 'uses' => 'PatientController@updateDentalChart']);
        // Update specific section of dental chart
        ApiRoute::post('patients/{id}/dental-chart-section', ['as' => 'api.patients.dental-chart-section.update', 'uses' => 'PatientController@updateDentalChartSection']);
    // Reset a tooth (delete all data for the given tooth id)
    ApiRoute::post('patients/{id}/dental-chart/reset-tooth', ['as' => 'api.patients.dental-chart.reset-tooth', 'uses' => 'PatientController@resetTooth']);
    
    // Dental Treat Monitor routes
    ApiRoute::post('patients/{id}/treat-monitor', ['as' => 'api.patients.treat-monitor.store', 'uses' => 'PatientController@createTreatMonitorItem']);
    ApiRoute::put('patients/{patientId}/treat-monitor/{itemId}', ['as' => 'api.patients.treat-monitor.update', 'uses' => 'PatientController@updateTreatMonitorItem']);
    ApiRoute::delete('patients/{patientId}/treat-monitor/{itemId}', ['as' => 'api.patients.treat-monitor.delete', 'uses' => 'PatientController@deleteTreatMonitorItem']);
    ApiRoute::post('patients/{patientId}/treat-monitor/{itemId}/resolve', ['as' => 'api.patients.treat-monitor.resolve', 'uses' => 'PatientController@resolveTreatMonitorItem']);
    ApiRoute::post('patients/{patientId}/treat-monitor/{itemId}/reactivate', ['as' => 'api.patients.treat-monitor.reactivate', 'uses' => 'PatientController@reactivateTreatMonitorItem']);
    
    // Patient Files Statistics
    ApiRoute::get('patient-files/stats', ['as' => 'api.patient-files.stats', 'uses' => 'PatientFileController@stats']);
    
    // Patient Next Appointment endpoint
    ApiRoute::get('patients/{id}/next-appointment', ['as' => 'api.patients.next-appointment', 'uses' => 'PatientController@getNextAppointment']);
    
    // Patient Messages (SMS)
    ApiRoute::get('messages/conversations', ['as' => 'api.messages.conversations', 'uses' => 'PatientMessageController@getConversations']);
    ApiRoute::get('patients/{patientId}/messages', ['as' => 'api.patients.messages.index', 'uses' => 'PatientMessageController@getPatientMessages']);
    ApiRoute::get('patients/{patientId}/messages/unread-count', ['as' => 'api.patients.messages.unread-count', 'uses' => 'PatientMessageController@getUnreadCount']);
    ApiRoute::post('patients/{patientId}/messages/mark-as-read', ['as' => 'api.patients.messages.mark-as-read', 'uses' => 'PatientMessageController@markAsRead']);
    ApiRoute::post('patients/messages/send', ['as' => 'api.patients.messages.send', 'uses' => 'PatientMessageController@sendMessage']);
    
    // Open Cases - Custom endpoint for active cases
    ApiRoute::get('patients/{patientId}/active-cases', ['as' => 'api.patients.active-cases', 'uses' => 'OpenCaseController@getActiveCases']);
    
    ApiRoute::resource('patients', 'PatientController', $options);
    ApiRoute::resource('insurance-providers', 'InsuranceProviderController', $options);
    ApiRoute::post('patient-notes/{xid}/highlight', ['as' => 'api.patient-notes.highlight', 'uses' => 'PatientNoteController@highlight']);
    ApiRoute::resource('patient-notes', 'PatientNoteController', $options);
    ApiRoute::resource('patient-histories', 'PatientHistoryController', $options);
    ApiRoute::resource('open-cases', 'OpenCaseController', $options);

        // Prescription endpoints
        ApiRoute::get('prescriptions/stats', ['as' => 'api.prescriptions.stats', 'uses' => 'PrescriptionController@getStats']);
        ApiRoute::get('prescriptions/{id}/download', ['as' => 'api.prescriptions.download', 'uses' => 'PrescriptionController@download']);
        ApiRoute::resource('prescriptions', 'PrescriptionController', $options);

        // Email endpoints
        ApiRoute::resource('emails', 'EmailController', $options);

        // Fax endpoints
        ApiRoute::post('faxes/store', ['as' => 'api.faxes.store_fax', 'uses' => 'FaxController@storeFax']);
        ApiRoute::put('faxes/{xid}/update', ['as' => 'api.faxes.update_fax', 'uses' => 'FaxController@updateFax']);
        ApiRoute::post('faxes/{id}/resend', ['as' => 'api.faxes.resend', 'uses' => 'FaxController@resend']);
        ApiRoute::post('faxes/webhook', ['as' => 'api.faxes.webhook', 'uses' => 'FaxController@webhook']);
        ApiRoute::resource('faxes', 'FaxController', $options);

        // Dental Treat Monitor endpoints
        ApiRoute::post('dental-treat-monitors/{id}/resolve', ['as' => 'api.dental-treat-monitors.resolve', 'uses' => 'DentalTreatMonitorController@resolve']);
        ApiRoute::post('dental-treat-monitors/{id}/reactivate', ['as' => 'api.dental-treat-monitors.reactivate', 'uses' => 'DentalTreatMonitorController@reactivate']);
        ApiRoute::resource('dental-treat-monitors', 'DentalTreatMonitorController', $options);

        // Patient Credit Cards endpoints
        ApiRoute::post('patients/{patient_id}/payment-methods/cards/{card_id}', ['as' => 'api.patient-credit-cards.update-card', 'uses' => 'PatientCreditCardController@updateCard']);
        ApiRoute::post('patients/{patient_id}/credit-cards/{card_id}/set-default', ['as' => 'api.patient-credit-cards.set-default', 'uses' => 'PatientCreditCardController@setAsDefault']);
        ApiRoute::apiResource('patient-credit-cards', 'PatientCreditCardController', $options);

        // Patient Check-ins endpoints
        ApiRoute::get('patient-check-ins/active', ['as' => 'api.patient-check-ins.active', 'uses' => 'PatientCheckInController@getActiveCheckIns']);
        ApiRoute::post('patient-check-ins/{id}/check-out', ['as' => 'api.patient-check-ins.check-out', 'uses' => 'PatientCheckInController@checkOut']);
        ApiRoute::apiResource('patient-check-ins', 'PatientCheckInController', $options);

        ApiRoute::resource('emergency-contacts', 'EmergencyContactController', $options);

        ApiRoute::put('appointments/{xid}/update-status', ['as' => 'api.appointments.update-status', 'uses' => 'AppointmentController@updateStatus']);
        
        ApiRoute::get('appointments/stats', ['as' => 'api.appointments.stats', 'uses' => 'AppointmentController@getStats']);
        ApiRoute::get('appointments/today', ['as' => 'api.appointments.today', 'uses' => 'AppointmentController@getAppointmentsByStatus']);
        
        // Appointment Items endpoints
        ApiRoute::post('appointments/items', ['as' => 'api.appointments.items.store', 'uses' => 'AppointmentItemController@storeItems']);
        ApiRoute::put('appointments/{xid}/items', ['as' => 'api.appointments.items.update', 'uses' => 'AppointmentItemController@updateItems']);
        ApiRoute::get('appointments/{xid}/items', ['as' => 'api.appointments.items.get', 'uses' => 'AppointmentItemController@getItems']);
        
        ApiRoute::resource('appointments', 'AppointmentController', $options);

        // Items endpoints
        ApiRoute::get('items/stats', ['as' => 'api.items.stats', 'uses' => 'ItemController@getStats']);
        ApiRoute::get('items/search-by-sku/{sku}', ['as' => 'api.items.search-by-sku', 'uses' => 'ItemController@searchBySku']);
        ApiRoute::resource('items', 'ItemController', $options);
        
        // Suppliers endpoints
        ApiRoute::get('suppliers/all', ['as' => 'api.suppliers.all', 'uses' => 'SupplierController@allOptions']);
        ApiRoute::resource('suppliers', 'SupplierController', $options);
        
        // Rooms endpoints
        ApiRoute::get('rooms/stats', ['as' => 'api.rooms.stats', 'uses' => 'RoomController@getStats']);
        ApiRoute::resource('rooms', 'RoomController', $options);
        
        // Mail Types endpoints
        ApiRoute::get('mail-types/all', ['as' => 'api.mail-types.all', 'uses' => 'MailTypeController@allOptions']);
        ApiRoute::resource('mail-types', 'MailTypeController', $options);
        
        ApiRoute::resource('postals', 'PostalController', $options);
        
        // Clinic Schedules Update Endpoint
        ApiRoute::post('clinic_schedules/update-schedules', ['as' => 'api.clinic_schedules.update-schedules', 'uses' => 'ClinicScheduleController@updateSchedules']);
        ApiRoute::resource('clinic_schedules', 'ClinicScheduleController', $options);

        ApiRoute::get('patient-file-types/all', ['as' => 'api.patient-file-types.all', 'uses' => 'PatientFileTypeController@allOptions']);
        ApiRoute::resource('patient-file-types', 'PatientFileTypeController', $options);
        ApiRoute::resource('patient-files', 'PatientFileController', $options);
        
        // Medicine endpoints
        ApiRoute::get('medicines/stats', ['as' => 'api.medicines.stats', 'uses' => 'MedicineController@getStats']);
        ApiRoute::get('medicines/search', ['as' => 'api.medicines.search', 'uses' => 'MedicineController@search']);
        ApiRoute::get('medicines/search-by-sku/{sku}', ['as' => 'api.medicines.search-by-sku', 'uses' => 'MedicineController@searchBySku']);
        ApiRoute::get('medicines/{xid}/batches', ['as' => 'api.medicines.batches', 'uses' => 'MedicineController@getBatches']);
        ApiRoute::resource('medicines', 'MedicineController', $options);
        
        ApiRoute::post('purchase-medicines/store', ['as' => 'api.purchase-medicines.store', 'uses' => 'PurchaseMedicineController@storeMedicine']);
        ApiRoute::put('purchase-medicines/{xid}/update', ['as' => 'api.purchase-medicines.update', 'uses' => 'PurchaseMedicineController@updateMedicine']);
        ApiRoute::resource('purchase-medicines', 'PurchaseMedicineController', $options);

        ApiRoute::post('item-categories/update-status', ['as' => 'api.item-categories.update-status', 'uses' => 'ItemCategoryController@updateStatus']);
        ApiRoute::get('item-categories/all', ['as' => 'api.item-categories.all', 'uses' => 'ItemCategoryController@allOptions']);
        ApiRoute::resource('item-categories', 'ItemCategoryController', $options);

        ApiRoute::post('room-types/update-status', ['as' => 'api.room-types.update-status', 'uses' => 'RoomTypeController@updateStatus']);
        ApiRoute::get('room-types/all', ['as' => 'api.room-types.all', 'uses' => 'RoomTypeController@allOptions']);
        ApiRoute::resource('room-types', 'RoomTypeController', $options);

        ApiRoute::get('rooms/stats', ['as' => 'api.rooms.stats', 'uses' => 'RoomController@getStats']);
        ApiRoute::resource('rooms', 'RoomController', $options);

        ApiRoute::post('item-brands/update-status', ['as' => 'api.item-brands.update-status', 'uses' => 'ItemBrandController@updateStatus']);
        ApiRoute::get('item-brands/all', ['as' => 'api.item-brands.all', 'uses' => 'ItemBrandController@allOptions']);
        ApiRoute::resource('item-brands', 'ItemBrandController', $options);

        ApiRoute::post('item-manufactures/update-status', ['as' => 'api.item-manufactures.update-status', 'uses' => 'ItemManufactureController@updateStatus']);
        ApiRoute::get('item-manufactures/all', ['as' => 'api.item-manufactures.all', 'uses' => 'ItemManufactureController@allOptions']);
        ApiRoute::resource('item-manufactures', 'ItemManufactureController', $options);
        
        ApiRoute::resource('items', 'ItemController', $options);

        ApiRoute::post('inventory-adjustments/quick-adjust', ['as' => 'api.inventory_adjustments.quick-adjust', 'uses' => 'InventoryAdjustmentController@quickAdjust']);
        ApiRoute::post('inventory-adjustments/store-adjustment', ['as' => 'api.inventory_adjustments.store_adjustment', 'uses' => 'InventoryAdjustmentController@storeAdjustment']);
        ApiRoute::put('inventory-adjustments/{xid}/update-adjustment', ['as' => 'api.inventory_adjustments.update-adjustment', 'uses' => 'InventoryAdjustmentController@updateAdjustment']);
        ApiRoute::resource('inventory-adjustments', 'InventoryAdjustmentController', $options);
        
        ApiRoute::post('adjustments-reason/update-status', ['as' => 'api.adjustments_reason.update-status', 'uses' => 'AdjustmentsReasonController@updateStatus']);
        ApiRoute::get('adjustments-reason/all', ['as' => 'api.adjustments_reason.all', 'uses' => 'AdjustmentsReasonController@allOptions']);
        ApiRoute::resource('adjustments-reason', 'AdjustmentsReasonController', $options);

        ApiRoute::resource('adjustment-items', 'AdjustmentItemController', $options);
        
        // Doctor Breaks endpoints - Specific routes first, then resource
        ApiRoute::get('doctor-breaks/doctors-with-breaks', ['as' => 'api.doctor-breaks.doctors-with-breaks', 'uses' => 'DoctorBreakController@getDoctorsWithBreaks']);
        ApiRoute::resource('doctor-breaks', 'DoctorBreakController', $options);
        
        // Doctor Holidays endpoints - Specific routes first, then resource
        ApiRoute::get('doctor-holidays/doctors-with-holidays', ['as' => 'api.doctor-holidays.doctors-with-holidays', 'uses' => 'DoctorHolidayController@getDoctorsWithHolidays']);
        ApiRoute::get('doctor-holidays/calendar-events', ['as' => 'api.doctor-holidays.calendar-events', 'uses' => 'DoctorHolidayController@getCalendarEvents']);
        ApiRoute::resource('doctor-holidays', 'DoctorHolidayController', $options);
        
        ApiRoute::resource('postals', 'PostalController', $options);
        
        // Doctor Specialties endpoints - Specific routes first, then resource
        ApiRoute::post('doctor-specialty/assign', ['as' => 'api.doctor-specialties.assign', 'uses' => 'DoctorSpecialtyController@assignSpecialties']);
        ApiRoute::get('doctor-specialty/all', ['as' => 'api.doctor-specialties.all', 'uses' => 'DoctorSpecialtyController@getAllSpecialties']);
        ApiRoute::get('doctor-specialty/doctor/{xid}', ['as' => 'api.doctor-specialties.doctor', 'uses' => 'DoctorSpecialtyController@getDoctorSpecialties']);
        ApiRoute::resource('doctor-specialty', 'DoctorSpecialtyController', $options);
        
        // Address Catalogs - Enhanced with new functionality
        
        // Countries endpoints
        ApiRoute::get('countries/all', ['as' => 'api.countries.all', 'uses' => 'CountryController@allOptions']);
        ApiRoute::get('countries/with-counts', ['as' => 'api.countries.with-counts', 'uses' => 'CountryController@withCounts']);
        ApiRoute::get('countries/statistics', ['as' => 'api.countries.statistics', 'uses' => 'CountryController@statistics']);
        ApiRoute::resource('countries', 'CountryController', $options);
        
        // States endpoints
        ApiRoute::get('states/all', ['as' => 'api.states.all', 'uses' => 'StateController@allOptions']);
        ApiRoute::resource('states', 'StateController', $options);
        
        ApiRoute::resource('addresses', 'AddressController', $options);

        ApiRoute::resource('expenses', 'ExpenseController', $options);

        ApiRoute::get('expense-categories/all', ['as' => 'api.expense-categories.all', 'uses' => 'ExpenseCategoryController@allOptions']);
        ApiRoute::post('expense-categories/update-status', ['as' => 'api.expense-categories.update-status', 'uses' => 'ExpenseCategoryController@updateStatus']);
        ApiRoute::resource('expense-categories', 'ExpenseCategoryController', $options);
        


    ApiRoute::resource('invoices', 'InvoiceController', $options);
    ApiRoute::resource('invoice_details', 'InvoiceDetailController', $options);
        
    // Sales resources
    ApiRoute::get('sales/dashboard/statistics', ['as' => 'api.sales.dashboard', 'uses' => 'SaleController@dashboard']);
    ApiRoute::resource('sales', 'SaleController', $options);
    ApiRoute::resource('sale_details', 'SaleDetailController', $options);
        ApiRoute::resource('treatment_types', 'TreatmentTypeController', $options);
        
        // Activity Logs endpoints
        ApiRoute::get('activity-logs/stats', ['as' => 'api.activity-logs.stats', 'uses' => 'ActivityLogController@getStats']);
        ApiRoute::resource('activity-logs', 'ActivityLogController', $options);
        
        // Address management endpoints
        ApiRoute::resource('addresses', 'AddressController', $options);
        
        // Calendar routes
        ApiRoute::resource('calendar', 'CalendarController', $options);
        ApiRoute::resource('calendar-events', 'CalendarEventController', $options);
        ApiRoute::get('calendar-data', ['as' => 'api.calendar.data', 'uses' => 'CalendarController@getCalendarData']);
        ApiRoute::get('calendar-stats', ['as' => 'api.calendar.stats', 'uses' => 'CalendarController@getCalendarStats']);
        ApiRoute::get('treatment-types', ['as' => 'api.treatment-types.index', 'uses' => 'CalendarController@getTreatmentTypes']);
        ApiRoute::get('check-slot-availability', ['as' => 'api.calendar.check-slot', 'uses' => 'CalendarController@checkSlotAvailability']);
    });
});

// Test routes without authentication (for testing purposes)
ApiRoute::group(['namespace' => 'App\Http\Controllers\Api'], function () {
    ApiRoute::get('test/countries', ['as' => 'api.test.countries', 'uses' => 'CountryController@allOptions']);
    ApiRoute::get('test/states', ['as' => 'api.test.states', 'uses' => 'StateController@allOptions']);
    ApiRoute::get('test/zip-codes', ['as' => 'api.test.zip-codes', 'uses' => 'ZipCodeController@allOptions']);
    ApiRoute::get('test/countries/statistics', ['as' => 'api.test.countries.statistics', 'uses' => 'CountryController@statistics']);
    ApiRoute::get('test/doctor-specialty/all', ['as' => 'api.test.doctor-specialties', 'uses' => 'DoctorSpecialtyController@getAllSpecialties']);
    ApiRoute::get('test/doctor-specialty/show/{xid}', ['as' => 'api.test.doctor-specialties.show', 'uses' => 'DoctorSpecialtyController@show']);

        
    // S3 Testing Route - Direct endpoint for testing S3 functionality
    ApiRoute::get('test-s3', ['as' => 'api.test.s3', 'uses' => 'S3TestController@testConnection']);
        
    // S3 Testing Routes - Direct endpoints for testing S3 functionality
    ApiRoute::post('test-s3/upload', ['as' => 'api.test.s3.upload', 'uses' => 'S3TestController@testUpload']);
    ApiRoute::post('test-s3/delete', ['as' => 'api.test.s3.delete', 'uses' => 'S3TestController@testDelete']);



});





