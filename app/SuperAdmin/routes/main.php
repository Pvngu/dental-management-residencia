<?php

use App\Classes\Common;
use App\Models\Company;
use App\Models\Lang;
use App\SuperAdmin\Models\GlobalCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('{path}', function () {
    
    $appName = "800dent";
    $appVersion = File::get(public_path() . '/superadmin_version.txt');
    $themeMode = session()->has('theme_mode') ? session('theme_mode') : 'light';
    $company = GlobalCompany::first();
    $appVersion = File::get('superadmin_version.txt');
    $appVersion = preg_replace("/\r|\n/", "", $appVersion);
    $globalCompanyLang = DB::table('companies')->select('lang_id')->where('is_global', 1)->first();
    $lang = $globalCompanyLang && $globalCompanyLang->lang_id && $globalCompanyLang->lang_id != null ? Lang::find($globalCompanyLang->lang_id) : Lang::first();
    // Logo
    $company = Company::withoutGlobalScope('company')
        ->where('is_global', 1)
        ->first();

    return view('welcome', [
        'appName' => $appName,
        'appVersion' => preg_replace("/\r|\n/", "", $appVersion),
        'themeMode' => $themeMode,
        'company' => $company,
        'appEnv' => env('APP_ENV'),
        'loadingLangMessageLang' => 'Loading...',
        'defaultLangKey' => $lang->key,
        'loadingImage' => $company ? $company->light_logo_url : null,
    ]);

})->where('path', '^(?!api.*$)(?!landing.*$).*')->name('main');
