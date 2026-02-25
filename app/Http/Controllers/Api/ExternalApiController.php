<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalApiController extends ApiBaseController
{
    /**
     * Get patient data by phone number
     * 
     * @param Request $request
     * @param string $phone
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientByPhone(Request $request, $phone)
    {
        try {
            // Get API client info from middleware
            $apiClient = $request->attributes->get('external_api_client');
            $companyId = $apiClient->company_id ?? null;

            // Sanitize phone number (remove spaces, dashes, etc.)
            $sanitizedPhone = preg_replace('/[^0-9+]/', '', $phone);
            
            Log::channel('external_api')->info('Patient lookup by phone', [
                'phone' => $sanitizedPhone,
                'ip' => $request->ip(),
                'company_id' => $companyId,
                'client_name' => $apiClient->name ?? 'Unknown',
                'timestamp' => now(),
            ]);

        // Base query for the user - filter by patients role from roles table
        $query = User::where('phone', $sanitizedPhone)
            ->whereHas('role', function ($q) {
                $q->where('name', 'patient');
            });

        // Apply company filtering if provided
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $user = $query->first();

        if (!$user) {
                Log::channel('external_api')->info('Patient not found', [
                    'phone' => $sanitizedPhone,
                    'ip' => $request->ip(),
                    'company_id' => $companyId,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Patient not found',
                ], 404);
            }

            // Get patient data
            $patient = Patient::where('user_id', $user->id)->first();

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'data' => [
                    'patient_id' => $user->xid,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'gender' => $user->gender,
                    'date_of_birth' => $user->date_of_birth,
                    'shipping_address' => $user->shipping_address,
                    'status' => $user->status,
                    'profile_image' => $user->profile_image,
                    'profile_image_url' => $user->profile_image_url,
                ],
            ];

            // Add patient-specific data if exists
            if ($patient) {
                $responseData['data']['patient_details'] = [
                    'allergies' => $patient->allergies,
                    'blood_type' => $patient->blood_type,
                    'pharmacy_name' => $patient->pharmacy_name,
                    'pharmacy_phone' => $patient->pharmacy_phone,
                    'media_channels' => $patient->media_channels,
                ];
            }

            Log::channel('external_api')->info('Patient data retrieved successfully', [
                'phone' => $sanitizedPhone,
                'patient_id' => $user->xid,
                'ip' => $request->ip(),
            ]);

            return response()->json($responseData, 200);

        } catch (\Exception $e) {
            Log::channel('external_api')->error('Error retrieving patient data', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving patient data',
            ], 500);
        }
    }
}
