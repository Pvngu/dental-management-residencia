<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Form;
use App\Models\User;
use App\Models\Doctor;
use App\Classes\Common;
use App\Classes\Files;
use App\Models\Company;
use App\Models\Patient;
use App\Models\Currency;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Examyou\RestAPI\ApiResponse;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ProfileRequest;
use App\Http\Requests\Api\Auth\UploadFileRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;

class AuthController extends ApiBaseController
{
    public function companySetting()
    {
        $settings = Company::first();

        return ApiResponse::make('Success', [
            'global_setting' => $settings,
        ]);
    }

    public function emailSettingVerified()
    {
        $emailSettingVerified = Settings::where('setting_type', 'email')
            ->where('status', 1)
            ->where('verified', 1)
            ->count();

        return $emailSettingVerified > 0 ? 1 : 0;
    }

    public function app()
    {
        $company = company(true);
        $addMenuSetting = $company ? Settings::where('setting_type', 'shortcut_menus')->first() : null;
        $totalCurrencies = Currency::count();

        // Start Filter Clinics Logic
        $user = auth('api')->user();
        if ($user && $company && $company->clinicLocations) {
            $canViewAll = $user->is_superadmin || $user->hasRole('admin') || $user->can('clinic_locations_view_all');

            if (!$canViewAll) {
                // Filter clinics to only those the user is assigned to
                // We use the many-to-many relationship we added to User model
                $userClinics = $user->clinics; // This returns a collection
                
                // We need to match them with the company's clinics (though they should overlap)
                // Simplest is to just replace the relation with user's clinics
                // But we must ensure they belong to this company (usually yes)
                $company->setRelation('clinicLocations', $userClinics);
            }
        }
        // End Filter Clinics Logic

        return ApiResponse::make('Success', [
            'app' => $company,
            'shortcut_menus' => $addMenuSetting,
            'email_setting_verified' => $this->emailSettingVerified(),
            'total_currencies' => $totalCurrencies,
            'google_maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }

    public function checkSubscriptionModuleVisibility()
    {
        $request = request();
        $itemType = $request->item_type;

        $visible = Common::checkSubscriptionModuleVisibility($itemType);

        return ApiResponse::make('Success', [
            'visible' => $visible,
        ]);
    }

    public function visibleSubscriptionModules()
    {
        $visibleSubscriptionModules = Common::allVisibleSubscriptionModules();

        return ApiResponse::make('Success', $visibleSubscriptionModules);
    }

    public function allEnabledLangs()
    {
        $availableLocales = [
            [
                'key' => 'en',
                'name' => 'English',
                'enabled' => 1,
                'image' => null
            ],
            [
                'key' => 'es',
                'name' => 'Español',
                'enabled' => 1,
                'image' => null
            ]
        ];

        return ApiResponse::make('Success', [
            'langs' => $availableLocales
        ]);
    }

    public function allForms()
    {
        $allForms = Form::select('id', 'name', 'form_fields')->get();

        return ApiResponse::make('Success', [
            'forms' => $allForms
        ]);
    }

    public function allUsers()
    {
        $request = request();
        $searchString = $request->has('searchString') ? $request->searchString : '';

        if ($request->has('log_type') && $request->log_type == 'doctor') {
            $query = Doctor::with(['user.role', 'user.addresses', 'doctorDepartment']);

            // Apply search filter if searchString is provided
            if ($searchString != '') {
                $query->where(function($q) use ($searchString) {
                    // Search in doctor fields
                    $q->where('doctors.qualification', 'LIKE', '%' . $searchString . '%')
                      ->orWhere('doctors.designation', 'LIKE', '%' . $searchString . '%')
                      ->orWhere('doctors.specialist', 'LIKE', '%' . $searchString . '%')
                      // Search in user fields (name, last_name, email, phone, full name)
                      ->orWhereHas('user', function($userQuery) use ($searchString) {
                          $userQuery->where('name', 'LIKE', '%' . $searchString . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $searchString . '%')
                                    ->orWhere('email', 'LIKE', '%' . $searchString . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $searchString . '%')
                                    ->orWhere(DB::raw("CONCAT(name, ' ', last_name)"), 'LIKE', '%' . $searchString . '%')
                                    // Search in addresses
                                    ->orWhereHas('addresses', function($addressQuery) use ($searchString) {
                                        $addressQuery->where('address_line_1', 'LIKE', '%' . $searchString . '%')
                                                    ->orWhere('address_line_2', 'LIKE', '%' . $searchString . '%')
                                                    ->orWhere('city', 'LIKE', '%' . $searchString . '%')
                                                    ->orWhere('state', 'LIKE', '%' . $searchString . '%')
                                                    ->orWhere('neighborhood', 'LIKE', '%' . $searchString . '%')
                                                    ->orWhere('postal_code', 'LIKE', '%' . $searchString . '%');
                                    });
                      });
                });
            }

            $users = $query->get()
                        ->map(function ($doctor) {
                            return [
                                'xid' => $doctor->xid,
                                'name' => $doctor->user->name,
                                'last_name' => $doctor->user->last_name,
                                'email' => $doctor->user->email,
                                'address' => $doctor->user->addresses,
                                'phone' => $doctor->user->phone,
                                'profile_image' => $doctor->user->profile_image,
                                'profile_image_url' => $doctor->user->profile_image_url,
                                'status' => $doctor->user->status,
                                'role' => $doctor->doctorDepartment->name ?? 'Doctor',
                            ];
                        })
                        ->groupBy('role');
        } else if ($request->has('log_type') && $request->log_type == 'patient') {
            $query = Patient::with(['user.role', 'user.addresses']);

            // Apply search filter if searchString is provided
            if ($searchString != '') {
                $query->whereHas('user', function($userQuery) use ($searchString) {
                    $userQuery->where('name', 'LIKE', '%' . $searchString . '%')
                              ->orWhere('last_name', 'LIKE', '%' . $searchString . '%')
                              ->orWhere('email', 'LIKE', '%' . $searchString . '%')
                              ->orWhere('phone', 'LIKE', '%' . $searchString . '%')
                              ->orWhere(DB::raw("CONCAT(name, ' ', last_name)"), 'LIKE', '%' . $searchString . '%')
                              // Search in addresses
                              ->orWhereHas('addresses', function($addressQuery) use ($searchString) {
                                  $addressQuery->where('address_line_1', 'LIKE', '%' . $searchString . '%')
                                              ->orWhere('address_line_2', 'LIKE', '%' . $searchString . '%')
                                              ->orWhere('city', 'LIKE', '%' . $searchString . '%')
                                              ->orWhere('state', 'LIKE', '%' . $searchString . '%')
                                              ->orWhere('neighborhood', 'LIKE', '%' . $searchString . '%')
                                              ->orWhere('postal_code', 'LIKE', '%' . $searchString . '%');
                              });
                });
            }

            $users = $query->get()
                        ->map(function ($patient) {
                            return [
                                'xid' => $patient->xid,
                                'name' => $patient->user->name,
                                'last_name' => $patient->user->last_name,
                                'email' => $patient->user->email,
                                'address' => $patient->user->addresses,
                                'phone' => $patient->user->phone,
                                'profile_image' => $patient->user->profile_image,
                                'profile_image_url' => $patient->user->profile_image_url,
                                'status' => $patient->user->status,
                                'role' => $patient->user->role->display_name ?? 'Patient',
                                'date_of_birth' => $patient->user->date_of_birth,
                            ];
                        })
                        ->groupBy('role');
        } else {
            $query = User::with(['role', 'addresses']);

            // Apply search filter if searchString is provided
            if ($searchString != '') {
                $query->where(function($q) use ($searchString) {
                    $q->where('name', 'LIKE', '%' . $searchString . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $searchString . '%')
                      ->orWhere('email', 'LIKE', '%' . $searchString . '%')
                      ->orWhere('phone', 'LIKE', '%' . $searchString . '%')
                      ->orWhere(DB::raw("CONCAT(name, ' ', last_name)"), 'LIKE', '%' . $searchString . '%')
                      // Search in addresses
                      ->orWhereHas('addresses', function($addressQuery) use ($searchString) {
                          $addressQuery->where('address_line_1', 'LIKE', '%' . $searchString . '%')
                                      ->orWhere('address_line_2', 'LIKE', '%' . $searchString . '%')
                                      ->orWhere('city', 'LIKE', '%' . $searchString . '%')
                                      ->orWhere('state', 'LIKE', '%' . $searchString . '%')
                                      ->orWhere('neighborhood', 'LIKE', '%' . $searchString . '%')
                                      ->orWhere('postal_code', 'LIKE', '%' . $searchString . '%');
                      });
                });
            }

            $users = $query->get()
                        ->map(function ($user) {
                            return [
                                'xid' => $user->xid,
                                'name' => $user->name,
                                'last_name' => $user->last_name,
                                'email' => $user->email,
                                'address' => $user->address,
                                'phone' => $user->phone,
                                'profile_image' => $user->profile_image,
                                'profile_image_url' => $user->profile_image_url,
                                'status' => $user->status,
                                'role' => $user->role->display_name ?? 'User',
                            ];
                        })
                        ->groupBy('role');
        }

        return ApiResponse::make('Success', [
            'users' => $users
        ]);
    }

    public function login(LoginRequest $request)
    {
        // Removing all sessions before login
        session()->flush();

        $phone = "";
        $email = "";

        $credentials = [
            'password' =>  $request->password
        ];

        if (is_numeric($request->get('email'))) {
            $credentials['phone'] = $request->email;
            $phone = $request->email;
        } else {
            $credentials['email'] = $request->email;
            $email = $request->email;
        }

        // For checking user
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        } else if ($phone != '') {
            $user = $user->where('phone', $phone);
        }
        $user = $user->first();

        // Adding user type according to email/phone
        $userCompany = null;
        if ($user) {
            $credentials['user_type'] = 'staff_members';
            $credentials['is_superadmin'] = 0;
            $userCompany = Company::withoutGlobalScope('company')->where('id', $user->company_id)->first();
        }

        $message = 'Loggged in successfull';
        $status = 'success';

        if (!$token = auth('api')->attempt($credentials)) {
            $status = 'fail';
            $message = 'These credentials do not match our records.';
        } else if (!$userCompany) {
            $status = 'fail';
            $message = 'Company not found.';
        } else if ($userCompany->status === 'pending') {
            $status = 'fail';
            $message = 'Your company not verified.';
        } else if ($userCompany->status === 'inactive') {
            $status = 'fail';
            $message = 'Company account deactivated.';
        } else if (auth('api')->user()->status === 'waiting') {
            $status = 'fail';
            $message = 'User not verified.';
        } else if (auth('api')->user()->status === 'disabled') {
            $status = 'fail';
            $message = 'Account deactivated.';
        }

        if ($status == 'success') {
            $company = company();
            $response = $this->respondWithToken($token);
            $addMenuSetting = Settings::where('setting_type', 'shortcut_menus')->first();
            $response['app'] = $company;
            $response['shortcut_menus'] = $addMenuSetting;
            $response['email_setting_verified'] = $this->emailSettingVerified();
            $response['visible_subscription_modules'] = Common::allVisibleSubscriptionModules();
        }
        $response['status'] = $status;
        $response['message'] = $message;

        return ApiResponse::make($message, $response);
    }

    protected function respondWithToken($token)
    {
        $user = user();

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()->addMinutes(480),
            'user' => $user
        ];
    }

    public function logout()
    {
        $request = request();

        if (auth('api')->user() && $request->bearerToken() != '') {
            auth('api')->logout();
        }

        session()->flush();

        return ApiResponse::make(__('Session closed successfully'));
    }

    public function user()
    {
        $user = auth('api')->user();
        $user = $user->load('roles', 'roles.permissions');

        session(['user' => $user]);

        return ApiResponse::make('Data successfull', [
            'user' => $user
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $newToken = auth('api')->refresh();

        $response = $this->respondWithToken($newToken);

        return ApiResponse::make('Token fetched successfully', $response);
    }

    public function uploadFile(UploadFileRequest $request)
    {
        $result = Common::uploadFile($request);

        return ApiResponse::make('File Uploaded', $result);
    }

    public function profile(ProfileRequest $request)
    {
        $user = auth('api')->user();

        $user->name = $request->name;
        
        // Handle file upload properly like DoctorController
        if ($request->hasFile('profile_image')) {
            $user->profile_image = Files::upload($request->file('profile_image'), 'users/profile-images');
        }
        
        if ($request->password != '') {
            $user->password = $request->password;
        }
        $user->phone = $request->phone;
        $user->address = $request->address;
        
        // Update country_code if provided
        if ($request->has('country_code')) {
            $user->country_code = $request->country_code;
        }
        
        // Update language preference
        if ($request->has('language')) {
            $user->language = $request->language;
        }
        
        $user->save();
        
        // Refresh the user to get the updated profile_image_url accessor
        $user->refresh();

        return ApiResponse::make('Profile updated successfull', [
            'user' => $user->load('role', 'role.permissions')
        ]);
    }

    public function preferences(Request $request)
    {
        $user = auth('api')->user();
        
        // Update language preference
        if ($request->has('language')) {
            $user->language = $request->language;
            $user->save();
        }

        return ApiResponse::make('Preferences updated successfully', [
            'user' => $user->load('role', 'role.permissions')
        ]);
    }

    public function dashboard(Request $request)
    {
        // start code here
    }

    public function getAllTimezones()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return ApiResponse::make('Success', [
            'timezones' => $timezones,
            'date_formates' => [
                'd-m-Y' => 'DD-MM-YYYY',
                'm-d-Y' => 'MM-DD-YYYY',
                'Y-m-d' => 'YYYY-MM-DD',
                'd.m.Y' => 'DD.MM.YYYY',
                'm.d.Y' => 'MM.DD.YYYY',
                'Y.m.d' => 'YYYY.MM.DD',
                'd/m/Y' => 'DD/MM/YYYY',
                'm/d/Y' => 'MM/DD/YYYY',
                'Y/m/d' => 'YYYY/MM/DD',
                'd/M/Y' => 'DD/MMM/YYYY',
                'd.M.Y' => 'DD.MMM.YYYY',
                'd-M-Y' => 'DD-MMM-YYYY',
                'd M Y' => 'DD MMM YYYY',
                'd F, Y' => 'DD MMMM, YYYY',
                'D/M/Y' => 'ddd/MMM/YYYY',
                'D.M.Y' => 'ddd.MMM.YYYY',
                'D-M-Y' => 'ddd-MMM-YYYY',
                'D M Y' => 'ddd MMM YYYY',
                'd D M Y' => 'DD ddd MMM YYYY',
                'D d M Y' => 'ddd DD MMM YYYY',
                'dS M Y' => 'Do MMM YYYY',
            ],
            'time_formates' => [
                "hh:mm A" => '12 Hours hh:mm A',
                'hh:mm a' => '12 Hours hh:mm a',
                'hh:mm:ss A' => '12 Hours hh:mm:ss A',
                'hh:mm:ss a' => '12 Hours hh:mm:ss a',
                'HH:mm ' => '24 Hours HH:mm:ss',
                'HH:mm:ss' => '24 Hours hh:mm:ss',
            ]
        ]);
    }

    public function downloadFile(Request $request)
    {
        $fileName = $request->file_name;
        $folderString = $request->folder;

        return (new Common())->downloadFile($fileName);
    }
}
