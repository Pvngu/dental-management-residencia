<?php

use Examyou\RestAPI\Facades\ApiRoute;

// Admin Subscription Route
ApiRoute::group(['namespace' => 'App\SuperAdmin\Http\Controllers\Api\Admin', 'middleware' => ['api.permission.check', 'api.auth.check']], function () {

    // Offline
    ApiRoute::post('submit-offline-request', ['as' => 'admin.extra.offline.submit-offline-request', 'uses' => 'AdminOfflineRequestController@submitOfflineRequest']);
    ApiRoute::resource('offline-requests', 'AdminOfflineRequestController', [
        'as' => 'api.extra',
        'only' => ['index']
    ]);

    // Paypal
    ApiRoute::get('paypal-invoice-download/{id}', ['as' => 'admin.extra.paypal.invoice-download', 'uses' => 'AdminPaypalController@paypalInvoiceDownload']);
    ApiRoute::get('paypal-recurring', ['as' => 'admin.extra.paypal-recurring', 'uses' => 'AdminPaypalController@payWithPaypalRecurrring']);
    ApiRoute::get('paypal/{planId}/{type}', ['as' => 'admin.extra.paypal', 'uses' => 'AdminPaypalController@paymentWithpaypal']);

    ApiRoute::post('razorpay-subscription', ['as' => 'api.extra.razorpay.subscription', 'uses' => 'RazorpaySubscriptionController@razorpaySubscription']);
    ApiRoute::post('razorpay-payment', ['as' => 'api.extra.razorpay.payment', 'uses' => 'RazorpaySubscriptionController@razorpayPayment']);

    // Stripe
    ApiRoute::post('stripe-payment', ['as' => 'api.extra.stripe.payment', 'uses' => 'AdminStripeController@stripePayment']);

    ApiRoute::get('all-payment-methods', ['as' => 'api.extra.subscription-plan.payment-methods', 'uses' => 'AdminSubscriptionController@allPaymentMethodSettings']);
    ApiRoute::get('all-subscription-plans', ['as' => 'api.extra.subscription-plan.all', 'uses' => 'AdminSubscriptionController@allSubscriptionPlans']);
    ApiRoute::get('subscription-plan-details', ['as' => 'api.extra.subscription-plan.details', 'uses' => 'AdminSubscriptionController@subscribePlanDetails']);
    ApiRoute::resource('subscription-transcations', 'AdminSubscriptionController', [
        'as' => 'api.extra',
        'only' => ['index']
    ]);
});

// Superadmin
ApiRoute::group(['namespace' => 'App\SuperAdmin\Http\Controllers\Api', 'prefix' => 'superadmin', 'middleware' => ['api.superadmin.check']], function () {
    $options = [
        'as' => 'api.superadmin'
    ];

    // For White Label Logo
    ApiRoute::get('default-logo-details', ['as' => 'api.superadmin.default-logo-details', 'uses' => 'DashboardController@defaultLogoDetails']);
    ApiRoute::post('upload-default-logo', ['as' => 'api.superadmin.upload-default-logo', 'uses' => 'DashboardController@uploadDefaultLogo']);
    ApiRoute::post('white-label-completed', ['as' => 'api.superadmin.white-label-completed', 'uses' => 'DashboardController@whiteLabelCompleted']);

    // Offline Requests
    ApiRoute::post('offline-requests/reject', ['as' => 'api.superadmin.offline-requests.reject', 'uses' => 'OfflineRequestController@rejectOfflineRequest']);
    ApiRoute::post('offline-requests/approve', ['as' => 'api.superadmin.offline-requests.approve', 'uses' => 'OfflineRequestController@approveOfflineRequest']);
    ApiRoute::resource('offline-requests', 'OfflineRequestController', ['as' => 'api.superadmin', 'only' => ['index']]);

     // Patient Credit Cards endpoints
    // ApiRoute::post('patients/{patient_id}/credit-cards/{card_id}/set-default', ['as' => 'api.patient-credit-cards.set-default', 'uses' => 'PatientCreditCardController@setAsDefault']);
    // ApiRoute::resource('patient-credit-cards', 'PatientCreditCardController', $options);


    // Commpanies
    ApiRoute::post('companies/change-subscription-plan', ['as' => 'api.superadmin.companies.change-subscription-plan', 'uses' => 'CompanyController@changeSubscriptionPlan']);
    ApiRoute::resource('companies', 'CompanyController', $options);

    ApiRoute::get('dashboard', ['as' => 'api.superadmin.dashboard', 'uses' => 'DashboardController@dashboard']);

    ApiRoute::get('global-company/website-lang', ['as' => 'api.superadmin.global-company.website-lang', 'uses' => 'GlobalCompanyController@getWebsiteLang']);
    ApiRoute::resource('global-company', 'GlobalCompanyController', ['as' => 'api.superadmin', 'only' => ['update']]);
    // ApiRoute::resource('langs', 'LangsController', $options);
    ApiRoute::resource('currencies', 'CurrencyController', $options);
    ApiRoute::resource('users', 'UsersController', $options);
    ApiRoute::resource('subscription-plans', 'SubscriptionPlanController', $options);
    ApiRoute::resource('offline-payment-modes', 'OfflinePaymentModeController', $options);

    ApiRoute::resource('payment-transcations', 'PaymentTranscationController', ['as' => 'api.superadmin', 'only' => ['index']]);

    ApiRoute::get('trial-plan', ['as' => 'api.subscription-plans.trial', 'uses' => 'SubscriptionPlanController@trailPlan']);

    ApiRoute::post('email-queries/send-email', ['as' => 'api.email-queries', 'uses' => 'EmailQueryController@sendContactMessage']);
    ApiRoute::resource('email-queries', 'EmailQueryController', ['as' => 'api.superadmin', 'only' => ['index']]);

    ApiRoute::group(['prefix' => 'payment-settings'], function () {
        ApiRoute::post('paypal/update', ['as' => 'api.payment-settings.paypal.update', 'uses' => 'PaymentSettingsController@updatePaypal']);
        ApiRoute::get('paypal', ['as' => 'api.payment-settings.paypal.index', 'uses' => 'PaymentSettingsController@getPaypal']);
        ApiRoute::post('stripe/update', ['as' => 'api.payment-settings.stripe.update', 'uses' => 'PaymentSettingsController@updateStripe']);
        ApiRoute::get('stripe', ['as' => 'api.payment-settings.stripe.index', 'uses' => 'PaymentSettingsController@getStripe']);
        ApiRoute::post('razorpay/update', ['as' => 'api.payment-settings.razorpay.update', 'uses' => 'PaymentSettingsController@updateRazorpay']);
        ApiRoute::get('razorpay', ['as' => 'api.payment-settings.razorpay.index', 'uses' => 'PaymentSettingsController@getRazorpay']);
        ApiRoute::post('paystack/update', ['as' => 'api.payment-settings.paystack.update', 'uses' => 'PaymentSettingsController@updatePaystack']);
        ApiRoute::get('paystack', ['as' => 'api.payment-settings.paystack.index', 'uses' => 'PaymentSettingsController@getPaystack']);
        ApiRoute::post('mollie/update', ['as' => 'api.payment-settings.mollie.update', 'uses' => 'PaymentSettingsController@updateMollie']);
        ApiRoute::get('mollie', ['as' => 'api.payment-settings.mollie.index', 'uses' => 'PaymentSettingsController@getMollie']);
        ApiRoute::post('authorize/update', ['as' => 'api.payment-settings.authorize.update', 'uses' => 'PaymentSettingsController@updateAuthorize']);
        ApiRoute::get('authorize', ['as' => 'api.payment-settings.authorize.index', 'uses' => 'PaymentSettingsController@getAuthorize']);
    });


       
        // Questionnaire Template specific actions
        ApiRoute::get('questionnaire-templates/{xid}/structure', ['as' => 'api.questionnaire-templates.structure', 'uses' => 'QuestionnaireTemplateController@getTemplateStructure']);
        ApiRoute::get('questionnaire-templates/{xid}/sections', ['as' => 'api.questionnaire-templates.sections', 'uses' => 'QuestionnaireTemplateController@getSections']);
        ApiRoute::post('questionnaire-templates/{xid}/sections', ['as' => 'api.questionnaire-templates.add-section', 'uses' => 'QuestionnaireTemplateController@addSection']);
        ApiRoute::patch('questionnaire-templates/{xid}/sections/reorder', ['as' => 'api.questionnaire-templates.reorder-sections', 'uses' => 'QuestionnaireTemplateController@reorderSections']);
        ApiRoute::post('questionnaire-templates/{xid}/clone', ['as' => 'api.questionnaire-templates.clone', 'uses' => 'QuestionnaireTemplateController@cloneTemplate']);
        
        ApiRoute::resource('questionnaire-templates', 'QuestionnaireTemplateController', $options);
           
        
        // Questionnaire Section specific actions
        ApiRoute::get('questionnaire-sections/{xid}/structure', ['as' => 'api.questionnaire-sections.structure', 'uses' => 'QuestionnaireSectionController@getSectionStructure']);
        ApiRoute::get('questionnaire-sections/{xid}/questions', ['as' => 'api.questionnaire-sections.questions', 'uses' => 'QuestionnaireSectionController@getQuestions']);
        ApiRoute::post('questionnaire-sections/{xid}/questions', ['as' => 'api.questionnaire-sections.add-question', 'uses' => 'QuestionnaireSectionController@addQuestion']);
        ApiRoute::patch('questionnaire-sections/{xid}/questions/reorder', ['as' => 'api.questionnaire-sections.reorder-questions', 'uses' => 'QuestionnaireSectionController@reorderQuestions']);
        
        ApiRoute::resource('questionnaire-sections', 'QuestionnaireSectionController', $options);

        // Questionnaire Instances with specific actions
        ApiRoute::post('questionnaire-instances/{xid}/assignments', ['as' => 'api.questionnaire-instances.generate-assignments', 'uses' => 'QuestionnaireInstanceController@generateAssignments']);
        ApiRoute::patch('questionnaire-instances/{xid}/close', ['as' => 'api.questionnaire-instances.close', 'uses' => 'QuestionnaireInstanceController@closeInstance']);
        ApiRoute::patch('questionnaire-instances/{xid}/reopen', ['as' => 'api.questionnaire-instances.reopen', 'uses' => 'QuestionnaireInstanceController@reopenInstance']);
        ApiRoute::get('questionnaire-instances/{xid}/stats', ['as' => 'api.questionnaire-instances.stats', 'uses' => 'QuestionnaireInstanceController@getInstanceStats']);
        ApiRoute::get('questionnaire-instances/{xid}/export', ['as' => 'api.questionnaire-instances.export', 'uses' => 'QuestionnaireInstanceController@exportResults']);
        ApiRoute::post('questionnaire-instances/{xid}/send-reminders', ['as' => 'api.questionnaire-instances.send-reminders', 'uses' => 'QuestionnaireInstanceController@sendReminders']);
        ApiRoute::resource('questionnaire-instances', 'QuestionnaireInstanceController', $options);
        
       
        
        // Question specific actions  
        ApiRoute::get('questions/{xid}/structure', ['as' => 'api.questions.structure', 'uses' => 'QuestionController@getQuestionStructure']);
        ApiRoute::get('questions/{xid}/options', ['as' => 'api.questions.options', 'uses' => 'QuestionController@getOptions']);
        ApiRoute::post('questions/{xid}/options', ['as' => 'api.questions.add-option', 'uses' => 'QuestionController@addOption']);
        ApiRoute::put('questions/{questionXid}/options/{optionXid}', ['as' => 'api.questions.update-option', 'uses' => 'QuestionController@updateOption']);
        ApiRoute::delete('questions/{questionXid}/options/{optionXid}', ['as' => 'api.questions.delete-option', 'uses' => 'QuestionController@deleteOption']);
        ApiRoute::patch('questions/{xid}/options/reorder', ['as' => 'api.questions.reorder-options', 'uses' => 'QuestionController@reorderOptions']);
        
        ApiRoute::resource('questions', 'QuestionController', $options);


        // Question Options CRUD
        ApiRoute::resource('question-options', 'QuestionOptionController', $options);






});
