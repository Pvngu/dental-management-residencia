<?php

namespace App\Http\Controllers\Api;

use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\User;
use App\Models\Role;
use App\Models\EmergencyContact;
use App\Scopes\CompanyScope;
use App\Traits\CompanyTraits;
use App\Traits\AddressTraits;
use App\Classes\Files;
use Illuminate\Http\Request;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\Patient\StoreRequest;
use App\Http\Requests\Api\Patient\UpdateRequest;
use App\Http\Requests\Api\Patient\DentalChartUpdateRequest;

// use function Laravel\Prompts\error;

class PatientController extends ApiBaseController
{
    use CompanyTraits, AddressTraits;

    protected $model = Patient::class;
    
    /**
     * Get all appointments for a patient for the current day
     */
    public function getNextAppointment($id)
    {
        $id = $this->getIdFromHash($id);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $company = company();
        $nowInCompanyTz = \Carbon\Carbon::now($company->timezone);
        $todayDate = $nowInCompanyTz->toDateString();

        $appointments = $patient->appointments()
            ->where('status', '!=', 'cancelled')
            ->whereDate('appointment_date', $todayDate)
            ->orderBy('appointment_date', 'asc')
            ->with(['doctor.user', 'room', 'treatmentType', 'prescription.prescriptionItems.medicine'])
            ->get();

        if ($appointments->isEmpty()) {
            return ApiResponse::make('Success', [
                'appointments' => []
            ]);
        }

        return ApiResponse::make('Success', [
            'appointments' => $appointments,
        ]);
    }
    
    public function overview($id)
    {
        $id = $this->getIdFromHash($id);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }
        $patient = Patient::where('id', $id)->first();
        
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        // Get upcoming appointments
        $upcomingAppointments = $patient->appointments()
            ->where('appointment_date', '>=', now())
            ->with(['doctor.user', 'room', 'treatmentType'])
            ->orderBy('appointment_date', 'asc')
            ->take(1)
            ->get()
            ->map(function ($appointment) {
                return [
                    'title' => $appointment->treatmentType ? $appointment->treatmentType->name : 'N/A',
                    'date' => $appointment->appointment_date->format('Y/m/d'),
                    'time' => $appointment->start_time . ' - ' . $appointment->end_time,
                    'location' => $appointment->room ? $appointment->room->name : 'N/A',
                    'doctor' => $appointment->doctor && $appointment->doctor->user ? 'Dr. ' . $appointment->doctor->user->name : 'N/A',
                    'description' => $appointment->treatment_details,
                    'status' => $appointment->status
                ];
            })
            ->first();

        // Get recent completed appointments
        $recentAppointments = $patient->appointments()
            ->where('status', 'completed')
            ->with(['doctor.user', 'room', 'treatmentType'])
            ->orderBy('appointment_date', 'desc')
            ->take(1)
            ->get()
            ->map(function ($appointment) {
                return [
                    'title' => $appointment->treatmentType ? $appointment->treatmentType->name : 'N/A',
                    'date' => $appointment->appointment_date->format('Y/m/d'),
                    'time' => $appointment->start_time . ' - ' . $appointment->end_time,
                    'location' => $appointment->room ? $appointment->room->name : 'N/A',
                    'doctor' => $appointment->doctor && $appointment->doctor->user ? 'Dr. ' . $appointment->doctor->user->name : 'N/A',
                    'description' => $appointment->treatment_details,
                    'status' => $appointment->status
                ];
            })
            ->first();

        // Get appointment stats
        $totalAppointments = $patient->appointments()->count();
        $upcomingAppointmentsCount = $patient->appointments()->where('appointment_date', '>=', now())->count();
        $completedAppointmentsCount = $patient->appointments()->where('status', 'completed')->count();
        
        return ApiResponse::make('Success', [
            'upcomingAppointments' => $upcomingAppointments ? [$upcomingAppointments] : [],
            'recentAppointments' => $recentAppointments ? [$recentAppointments] : [],
            'stats' => [
                'total' => $totalAppointments,
                'upcoming' => $upcomingAppointmentsCount,
                'completed' => $completedAppointmentsCount
            ]
        ]);
    }

    /**
     * Return only the patient's dental chart conditions object
     */
    public function dentalChart($id)
    {
        $id = $this->getIdFromHash($id);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }
        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        // Get treat and monitor items for this patient
        $treatMonitorItems = $patient->dentalTreatMonitors()
            ->with(['creator:id,name', 'resolver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'xid' => $item->xid,
                    'tooth_number' => $item->tooth_number,
                    'type' => $item->type,
                    'status' => $item->status,
                    'content' => $item->content,
                    'comment' => $item->comment,
                    'created_at' => $item->created_at,
                    'resolved_at' => $item->resolved_at,
                    'creator' => $item->creator,
                    'resolver' => $item->resolver,
                ];
            });

        return ApiResponse::make('Success', [
            'conditions' => $patient->dental_chart ?? new \stdClass(),
            'treat_monitor_items' => $treatMonitorItems,
        ]);
    }

    /**
     * Update patient's dental chart saving only the conditions object
     */
    public function updateDentalChart(DentalChartUpdateRequest $request, $id)
    {
        $xid = $id;
        $id = $this->getIdFromHash($xid);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        // Store only the provided conditions object as JSON
        $patient->dental_chart = $request->conditions;
        $patient->save();

        return ApiResponse::make('Success', [
            'xid' => $patient->xid,
            'conditions' => $patient->dental_chart,
        ]);
    }

    /**
     * Update a specific section of a tooth in the dental chart
     */
    public function updateDentalChartSection($id)
    {
        $xid = $id;
        $id = $this->getIdFromHash($xid);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $request = request();
        
        // Validate request data
        $validatedData = $request->validate([
            'tooth_id' => 'required|integer',
            'section' => 'required|string|in:pathology,restoration,periodontal,endodontic_test,tests,conditions',
            'data' => 'nullable|array'
        ]);

        $toothId = $validatedData['tooth_id'];
        $section = $validatedData['section'];
        $sectionData = $validatedData['data'];

        // Get current dental chart or initialize as empty array
        $dentalChart = $patient->dental_chart ?? [];

        // Ensure dental chart is an array
        if (!is_array($dentalChart)) {
            $dentalChart = [];
        }

        // Find existing tooth
        $toothIndex = null;
        foreach ($dentalChart as $index => $tooth) {
            if (isset($tooth['id']) && $tooth['id'] == $toothId) {
                $toothIndex = $index;
                break;
            }
        }

        // If data is null or empty, remove the section from the tooth
        if ($this->isDataEmpty($sectionData)) {
            if ($toothIndex !== null) {
                $existingTooth = $dentalChart[$toothIndex];
                
                // Remove the section from the tooth
                unset($existingTooth[$section]);
                
                // If tooth has no data left (only id), remove the entire tooth
                if (count($existingTooth) <= 1 && isset($existingTooth['id'])) {
                    unset($dentalChart[$toothIndex]);
                    // Re-index the array to avoid gaps
                    $dentalChart = array_values($dentalChart);
                } else {
                    $dentalChart[$toothIndex] = $existingTooth;
                }
            }
        } else {
            // If tooth doesn't exist, create a new entry
            if ($toothIndex === null) {
                $dentalChart[] = [
                    'id' => $toothId,
                    $section => $sectionData
                ];
            } else {
                // For pathology section, replace completely instead of deep merge
                // to properly handle removal of deselected pathology types
                $existingTooth = $dentalChart[$toothIndex];
                
                if ($section === 'pathology') {
                    // Replace the entire pathology section
                    $existingTooth[$section] = $sectionData;
                } else {
                    // For other sections, use deep merge
                    if (!isset($existingTooth[$section])) {
                        $existingTooth[$section] = [];
                    }
                    
                    // Deep merge arrays recursively
                    $existingTooth[$section] = $this->deepMergeArrays($existingTooth[$section], $sectionData);
                }
                
                $dentalChart[$toothIndex] = $existingTooth;
            }
        }

        // Save the updated dental chart
        $patient->dental_chart = $dentalChart;
        $patient->save();

        return ApiResponse::make('Success', [
            'xid' => $patient->xid,
            'dental_chart' => $patient->dental_chart,
            'updated_tooth' => [
                'id' => $toothId,
                'section' => $section,
                'data' => $sectionData,
                'action' => $this->isDataEmpty($sectionData) ? 'removed' : 'updated'
            ]
        ]);
    }

    /**
     * Reset a specific tooth: remove all data for the tooth from the dental_chart
     */
    public function resetTooth(Request $request, $id)
    {
        $xid = $id;
        $id = $this->getIdFromHash($xid);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $validated = $request->validate([
            'tooth_id' => 'required|integer',
        ]);

        $toothId = $validated['tooth_id'];

        $dentalChart = $patient->dental_chart ?? [];
        if (!is_array($dentalChart)) {
            $dentalChart = [];
        }

        $found = false;
        foreach ($dentalChart as $index => $tooth) {
            if (isset($tooth['id']) && $tooth['id'] == $toothId) {
                unset($dentalChart[$index]);
                $found = true;
                break;
            }
        }

        // Reindex array
        $dentalChart = array_values($dentalChart);

        $patient->dental_chart = $dentalChart;
        $patient->save();

        return ApiResponse::make('Success', [
            'xid' => $patient->xid,
            'dental_chart' => $patient->dental_chart,
            'removed_tooth_id' => $found ? $toothId : null,
        ]);
    }

    /**
     * Deep merge two arrays recursively
     */
    private function deepMergeArrays($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = $this->deepMergeArrays($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * Check if data is considered empty and should not be saved
     */
    private function isDataEmpty($data)
    {
        if ($data === null || $data === [] || $data === '') {
            return true;
        }
        
        if (is_array($data)) {
            // Special handling for pathology data
            if (isset($data['selectedType'])) {
                return $this->isPathologyDataEmpty($data);
            }
            
            // Check if array is empty or contains only empty values
            foreach ($data as $value) {
                if (!$this->isDataEmpty($value)) {
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }

    /**
     * Check if pathology data has meaningful content
     */
    private function isPathologyDataEmpty($data)
    {
        if (!is_array($data)) {
            return true;
        }

        // Create a copy to work with
        $pathologyData = $data;
        
        // Remove selectedType as it's just UI state - don't count it as meaningful data
        unset($pathologyData['selectedType']);

        // If no pathology sections remain, it's empty
        if (empty($pathologyData)) {
            return true;
        }

        // Check if any pathology section has meaningful data
        foreach ($pathologyData as $sectionName => $sectionData) {
            if ($this->hasPathologyValues($sectionData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a pathology section has meaningful values
     */
    private function hasPathologyValues($sectionData)
    {
        if (!is_array($sectionData)) {
            return false;
        }

        foreach ($sectionData as $key => $value) {
            if (is_array($value)) {
                // For arrays (like selectedSurfaces), check if not empty
                if (!empty($value)) {
                    return true;
                }
            } else {
                // For scalar values, check if not null/empty
                if ($value !== null && $value !== '' && $value !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function modifyIndex($query)
    {
        $request = request();

        if ($request->has('searchString') && $request->searchString != "") {
            $query = $query->whereHas('user', function ($q) use ($request) {
                $term = '%' . $request->searchString . '%';
                $q->where('name', 'LIKE', $term)
                    ->orWhere('last_name', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term)
                    ->orWhere('phone', 'LIKE', $term)
                    ->orWhere(DB::raw("CONCAT(name, ' ', last_name)"), 'LIKE', $term)
                    ->orWhere('address', 'LIKE', $term)
                    ->orWhereHas('addresses', function ($q2) use ($term) {
                        $q2->where('address_line_1', 'LIKE', $term)
                            ->orWhere('address_line_2', 'LIKE', $term)
                            ->orWhere('city', 'LIKE', $term)
                            ->orWhere('state', 'LIKE', $term)
                            ->orWhere('postal_code', 'LIKE', $term)
                            ->orWhere('neighborhood', 'LIKE', $term);
                    });
            });
        }

        if($request->has('status') && $request->status != "") {
           if($request->status == 'incoming') {
               $query = $query->whereHas('appointments', function ($q) {
                   $q->where('status', 'pending')
                     ->whereDate('appointment_date', now()->toDateString());
               });
           }
        }
        
        return $query;
    }

    public function storePatient(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $company = company();

            $patientRole = Role::withoutGlobalScope(CompanyScope::class)
                ->where('name', 'patient')
                ->where('company_id', $company->id)
                ->first();


            // Create user first
            $user = new User();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
            $user->status = $request->status;
            $user->gender = $request->gender;
            $user->user_type = 'patients';
            $user->company_id = $company->id;
            $user->role_id = $patientRole ? $patientRole->id : null;
            $user->role_type = 'patient';
            if ($request->has('password') && $request->password != '') {
                $user->password = $request->password;
            }
            $user->address = $request->address;
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $user->profile_image = Files::upload($request->file('profile_image'), 'patients/profile-images');
            }
            
            $user->save();
            if ($patientRole) {
                $user->assignRole($patientRole->name, '');
            }

            // Handle addresses if provided
            if ($request->has('addresses')) {
                $this->handleAddresses($user, $request->addresses);
            }

            // Create patient
            $patient = new Patient();
            $patient->company_id = $company->id;
            $patient->user_id = $user->id;
            $patient->allergies = $request->allergies;
            $patient->blood_type = $request->blood_type;
            $patient->ssn = $request->ssn;
            
            // Pharmacy information
            $patient->pharmacy_name = $request->pharmacy_name;
            $patient->pharmacy_phone = $request->pharmacy_phone;
            $patient->preferred_doctor_id = $request->preferred_doctor_id ? $this->getIdFromHash($request->preferred_doctor_id) : null;
            
            // Structured channels information
            $structuredChannels = [];
            if ($request->has('structured_channels') && is_array($request->structured_channels)) {
                $structuredChannels = $request->structured_channels;
            }
            $patient->media_channels = $structuredChannels;
            
            // Dental chart (empty for now)
            $patient->dental_chart = [];
            
            $patient->save();

            // Handle insurance data
            $this->handlePatientInsurances($patient, $request);

            // Handle emergency contacts if provided
            if ($request->has('emergency_contacts')) {
                $this->handleEmergencyContacts($patient, $request->emergency_contacts);
            }

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $patient->xid
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed. ' . $e->getMessage());
        }
    }

    public function updatePatient(UpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $xid = $id;
            $id = $this->getIdFromHash($xid);
            $patient = Patient::find($id);
            if (!$patient) {
                throw new ApiException('Patient not found');
            }
            
            // Find and update user
            $user = User::find($patient->user_id);
            if (!$user) {
                throw new ApiException('User not found for this patient');
            }
            
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
            $user->status = $request->status;
            $user->gender = $request->gender;
            if ($request->has('password') && $request->password != '') {
                $user->password = $request->password;
            }
            // $user->address = $request->address;
            
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $user->profile_image = Files::upload($request->file('profile_image'), 'patients/profile-images');
            }
            
            $user->save();

            // Handle addresses if provided
            if ($request->has('addresses')) {
                $this->handleAddresses($user, $request->addresses);
            }

            // Update patient
            $patient->allergies = $request->allergies;
            $patient->blood_type = $request->blood_type;
            $patient->ssn = $request->ssn;
            
            // Pharmacy information
            $patient->pharmacy_name = $request->pharmacy_name;
            $patient->pharmacy_phone = $request->pharmacy_phone;
            $patient->preferred_doctor_id = $request->preferred_doctor_id ? $this->getIdFromHash($request->preferred_doctor_id) : null;
            
            // Structured channels information
            $structuredChannels = [];
            if ($request->has('structured_channels')) {
                $structuredChannels = $request->structured_channels;
            }
            $patient->media_channels = $structuredChannels;
            
            // Keep existing dental_chart or set empty if not exists
            if (!$patient->dental_chart) {
                $patient->dental_chart = [];
            }
            
            $patient->save();

            // Handle insurance data
            $this->handlePatientInsurances($patient, $request);

            // Handle emergency contacts if provided
            if ($request->has('emergency_contacts')) {
                $this->handleEmergencyContacts($patient, $request->emergency_contacts);
            }

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $patient->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed. ' . $e->getMessage());
        }
    }

    /**
     * Handle addresses for any addressable entity (User, Doctor, Patient)
     */


    /**
     * Handle emergency contacts for patient
     */
    private function handleEmergencyContacts($patient, $emergencyContacts)
    {
        $company = company();
        
        if (is_array($emergencyContacts) && count($emergencyContacts) > 0) {
            // Delete existing emergency contacts for this patient
            $patient->emergencyContacts()->delete();
            
            foreach ($emergencyContacts as $contactData) {
                $emergencyContact = new EmergencyContact();
                $emergencyContact->patient_id = $patient->id;
                $emergencyContact->company_id = $company->id;
                $emergencyContact->name = $contactData['name'] ?? '';
                $emergencyContact->phone = $contactData['phone'] ?? '';
                $emergencyContact->relation = $contactData['relation'] ?? '';
                $emergencyContact->save();
            }
        } else {
            // If no emergency contacts provided, delete existing ones
            $patient->emergencyContacts()->delete();
        }
    }

    /**
     * Handle patient insurances
     */
    private function handlePatientInsurances($patient, $request)
    {
        $company = company();
        
        // Delete existing insurances for this patient
        $patient->insurances()->delete();
        
        // Determine which insurance should be primary based on user selection
        // Default to 'primary' if not specified
        $primarySelection = $request->primary_insurance ?? 'primary';
        
        // Handle primary insurance
        if ($request->has('provider_id') && !empty($request->provider_id)) {
            $providerId = $this->getIdFromHash($request->provider_id);
            
            $isPrimaryForFirst = ($primarySelection === 'primary');
            
            $primaryInsurance = new PatientInsurance();
            $primaryInsurance->patient_id = $patient->id;
            $primaryInsurance->provider_id = $providerId;
            $primaryInsurance->company_id = $company->id;
            $primaryInsurance->policy_holder_name = $request->policy_holder_name;
            $primaryInsurance->relationship_to_holder = $request->relationship_to_holder ?? 'self';
            $primaryInsurance->member_id = $request->member_id;
            $primaryInsurance->group_number = $request->group_number;
            $primaryInsurance->plan_type = $request->plan_type;
            $primaryInsurance->is_primary = $isPrimaryForFirst;
            $primaryInsurance->verified_at = $request->verified_at ? date('Y-m-d H:i:s', strtotime($request->verified_at)) : null;
            $primaryInsurance->is_active = true;
            $primaryInsurance->save();
        }
        
        // Handle secondary insurance
        if ($request->has('secondary_provider_id') && !empty($request->secondary_provider_id)) {
            $secondaryProviderId = $this->getIdFromHash($request->secondary_provider_id);
            
            $isPrimaryForSecond = ($primarySelection === 'secondary');
            
            $secondaryInsurance = new PatientInsurance();
            $secondaryInsurance->patient_id = $patient->id;
            $secondaryInsurance->provider_id = $secondaryProviderId;
            $secondaryInsurance->company_id = $company->id;
            $secondaryInsurance->policy_holder_name = $request->secondary_policy_holder_name;
            $secondaryInsurance->relationship_to_holder = $request->secondary_relationship_to_holder ?? 'self';
            $secondaryInsurance->member_id = $request->secondary_member_id;
            $secondaryInsurance->group_number = $request->secondary_group_number;
            $secondaryInsurance->plan_type = $request->secondary_plan_type;
            $secondaryInsurance->is_primary = $isPrimaryForSecond;
            $secondaryInsurance->verified_at = $request->secondary_verified_at ? date('Y-m-d H:i:s', strtotime($request->secondary_verified_at)) : null;
            $secondaryInsurance->is_active = true;
            $secondaryInsurance->save();
        }
    }

    /**
     * Create a new treat monitor item for the patient
     */
    public function createTreatMonitorItem(Request $request, $id)
    {
        $company = company();
        $xid = $id;
        $id = $this->getIdFromHash($xid);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $validated = $request->validate([
            'tooth_number' => 'required|string|max:5',
            'type' => 'required|in:urgent,important,normal',
            'content' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $treatMonitorItem = new \App\Models\DentalTreatMonitor();
        $treatMonitorItem->patient_id = $patient->id;
        $treatMonitorItem->tooth_number = $validated['tooth_number'];
        $treatMonitorItem->type = $validated['type'];
        $treatMonitorItem->content = $validated['content'];
        $treatMonitorItem->comment = $validated['comment'] ?? null;
        $treatMonitorItem->created_by = User()->id;
        $treatMonitorItem->company_id = $company->id;
        $treatMonitorItem->save();

        return ApiResponse::make('Success', [
            'xid' => $treatMonitorItem->xid,
            'message' => 'Treat monitor item created successfully'
        ]);
    }

    /**
     * Update a treat monitor item
     */
    public function updateTreatMonitorItem(Request $request, $patientId, $itemId)
    {
        $patientId = $this->getIdFromHash($patientId);
        $itemId = $this->getIdFromHash($itemId);
        
        if (!$patientId || !$itemId) {
            throw new ApiException('Invalid patient or item ID', null, 400);
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $treatMonitorItem = $patient->dentalTreatMonitors()->find($itemId);
        if (!$treatMonitorItem) {
            throw new ApiException('Treat monitor item not found', null, 404);
        }

        $validated = $request->validate([
            'tooth_number' => 'required|string|max:5',
            'type' => 'required|in:urgent,important,normal',
            'content' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $treatMonitorItem->tooth_number = $validated['tooth_number'];
        $treatMonitorItem->type = $validated['type'];
        $treatMonitorItem->content = $validated['content'];
        $treatMonitorItem->comment = $validated['comment'] ?? null;
        $treatMonitorItem->updated_by = User()->id;
        $treatMonitorItem->save();

        return ApiResponse::make('Success', [
            'xid' => $treatMonitorItem->xid,
            'message' => 'Treat monitor item updated successfully'
        ]);
    }

    /**
     * Delete a treat monitor item
     */
    public function deleteTreatMonitorItem($patientId, $itemId)
    {
        $patientId = $this->getIdFromHash($patientId);
        $itemId = $this->getIdFromHash($itemId);
        
        if (!$patientId || !$itemId) {
            throw new ApiException('Invalid patient or item ID', null, 400);
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $treatMonitorItem = $patient->dentalTreatMonitors()->find($itemId);
        if (!$treatMonitorItem) {
            throw new ApiException('Treat monitor item not found', null, 404);
        }

        $treatMonitorItem->deleted_by = User()->id;
        $treatMonitorItem->save();
        $treatMonitorItem->delete();

        return ApiResponse::make('Success', [
            'message' => 'Treat monitor item deleted successfully'
        ]);
    }

    /**
     * Resolve a treat monitor item
     */
    public function resolveTreatMonitorItem($patientId, $itemId)
    {
        $patientId = $this->getIdFromHash($patientId);
        $itemId = $this->getIdFromHash($itemId);
        
        if (!$patientId || !$itemId) {
            throw new ApiException('Invalid patient or item ID', null, 400);
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $treatMonitorItem = $patient->dentalTreatMonitors()->find($itemId);
        if (!$treatMonitorItem) {
            throw new ApiException('Treat monitor item not found', null, 404);
        }

        $treatMonitorItem->status = 'resolved';
        $treatMonitorItem->resolved_at = now();
        $treatMonitorItem->resolved_by = User()->id;
        $treatMonitorItem->updated_by = User()->id;
        $treatMonitorItem->save();

        return ApiResponse::make('Success', [
            'xid' => $treatMonitorItem->xid,
            'message' => 'Treat monitor item resolved successfully'
        ]);
    }

    /**
     * Reactivate a treat monitor item
     */
    public function reactivateTreatMonitorItem($patientId, $itemId)
    {
        $patientId = $this->getIdFromHash($patientId);
        $itemId = $this->getIdFromHash($itemId);
        
        if (!$patientId || !$itemId) {
            throw new ApiException('Invalid patient or item ID', null, 400);
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        $treatMonitorItem = $patient->dentalTreatMonitors()->find($itemId);
        if (!$treatMonitorItem) {
            throw new ApiException('Treat monitor item not found', null, 404);
        }

        $treatMonitorItem->status = 'active';
        $treatMonitorItem->resolved_at = null;
        $treatMonitorItem->resolved_by = null;
        $treatMonitorItem->updated_by = User()->id;
        $treatMonitorItem->save();

        return ApiResponse::make('Success', [
            'xid' => $treatMonitorItem->xid,
            'message' => 'Treat monitor item reactivated successfully'
        ]);
    }

    /**
     * Get patient payment methods
     */
    public function getPaymentMethods($id)
    {
        $id = $this->getIdFromHash($id);
        if (!$id) {
            throw new ApiException('Invalid patient ID', null, 400);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            throw new ApiException('Patient not found', null, 404);
        }

        // Get credit cards
        $creditCards = $patient->creditCards()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($card) {
                return [
                    'xid' => $card->xid,
                    'card_type' => $card->card_type,
                    'last_four' => $card->last_four_digits,
                    'exp_month' => $card->expiry_month,
                    'exp_year' => $card->expiry_year,
                    'name_on_card' => $card->name_on_card,
                    'is_default' => $card->is_default,
                    'is_expired' => $this->isCardExpired($card->expiry_month, $card->expiry_year)
                ];
            });

        // Get bank accounts
        $bankAccounts = $patient->bankAccounts()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'last_four' => $account->last_four_digits,
                    'account_type' => $account->account_type,
                    'account_holder_name' => $account->account_holder_name,
                    'is_default' => $account->is_default
                ];
            });

        // Get PayPal accounts
        $paypalAccounts = $patient->paypalAccounts()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'email' => $account->email,
                    'is_default' => $account->is_default
                ];
            });

        return ApiResponse::make('Success', [
            'credit_cards' => $creditCards,
            'bank_accounts' => $bankAccounts,
            'paypal_accounts' => $paypalAccounts
        ]);
    }

    /**
     * Check if credit card is expired
     */
    private function isCardExpired($month, $year)
    {
        // Convert 2-digit year to 4-digit if needed
        if (strlen($year) == 2) {
            $year = '20' . $year;
        }

        $expirationDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();
        return now()->greaterThan($expirationDate);
    }
}
