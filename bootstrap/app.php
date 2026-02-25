<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        using: function () {
            // API routes without middleware (for webhooks, etc.)
            Route::prefix('')->group(base_path('routes/app.php'));
            
            Route::middleware('web')
                ->namespace('App\\SuperAdmin\\Http\\Controllers')
                ->group(base_path('app/SuperAdmin/routes/main.php'));

            // Added routes from AppServiceProvider
            Route::middleware('web')
                ->namespace('App\\Http\\Controllers')
                ->group(base_path('routes/common.php'));

            Route::middleware('web')
                ->namespace('App\\Http\\Controllers')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->namespace('App\\SuperAdmin\\Http\\Controllers')
                ->group(base_path('app/SuperAdmin/routes/app.php'));

            Route::middleware('web')
                ->namespace('App\\SuperAdmin\\Http\\Controllers')
                ->group(base_path('app/SuperAdmin/routes/superadmin.php'));

            Route::middleware('web')
                ->namespace('App\\SuperAdmin\\Http\\Controllers')
                ->group(base_path('app/SuperAdmin/routes/front.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'api.customer' => \App\Http\Middleware\ApiCustomerMiddleware::class,
            'api.super_admin' => \App\Http\Middleware\ApiSuperAdminMiddleware::class,
            'checkpermission' => \App\Http\Middleware\CheckPermission::class,
            'license.expire.date.wise' => \App\Http\Middleware\LicenseExpireDateWise::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            
            'api.auth.check' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'api.permission.check' => \App\Http\Middleware\CheckPermission::class,
            'api.superadmin.check' => \App\Http\Middleware\ApiSuperAdminMiddleware::class,
            'license-expire' => \App\Http\Middleware\LicenseExpireDateWise::class,
            'external.api.token' => \App\Http\Middleware\ExternalApiToken::class,
            'set.clinic' => \App\Http\Middleware\SetClinicContext::class,
        ]);

        $middleware->append([
            \App\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->priority([
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
