<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Models\TreatmentType;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    /**
     * Display the landing page for the specified company.
     *
     * @param string $companySlug
     * @return \Illuminate\Http\Response
     */
    public function show($companySlug)
    {
        try {
            // If the slug is 'example', always use company with ID 1
            if ($companySlug === 'example') {
                $company = Company::find(1);
                
                // If company with ID 1 doesn't exist, return 404
                if (!$company) {
                    Log::error('Example company (ID 1) not found');
                    abort(404, 'Example company not found');
                }
            } else {
                // Otherwise, get the company by slug (first check if company exists at all)
                $companyExists = Company::where('company_slug', $companySlug)->first();
                
                if (!$companyExists) {
                    Log::error('Company not found with slug: ' . $companySlug);
                    return response()->view('errors.company-not-found', [
                        'companySlug' => $companySlug,
                        'reason' => 'not_found'
                    ], 404);
                }
                
                // Now check if landing page is enabled
                $company = Company::where('company_slug', $companySlug)->where('enable_landing_page', true)->first();

                if (!$company) {
                    Log::error('Landing page disabled for company with slug: ' . $companySlug);
                    return response()->view('errors.company-not-found', [
                        'companySlug' => $companySlug,
                        'reason' => 'disabled',
                        'companyName' => $companyExists->name
                    ], 403);
                }
            }
            
            // Check if landing pages are enabled for this company
            if (!$company->enable_landing_page) {
                abort(403, 'Landing pages are disabled for this company');
            }
            
            // Make sure company has a slug set
            if (!$company->company_slug) {
                $company->company_slug = $companySlug === 'example' ? 'example' : Str::slug($company->name);
            }
            
            // Return the landing page view
            return view('landing', [
                'company' => $company
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing landing page: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error loading landing page: ' . $e->getMessage());
        }
    }

    /**
     * Get company data for API calls
     * 
     * @param string $companySlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyData($companySlug)
    {
        // If the slug is 'example', always use company with ID 1
        if ($companySlug === 'example') {
            $company = Company::find(1);
            
            // If company with ID 1 doesn't exist, return 404
            if (!$company) {
                return response()->json(['message' => 'Example company not found'], 404);
            }
        } else {
            // Otherwise, get the company by slug
            $company = Company::where('slug', $companySlug)->first();
            
            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }
        }
        
        // Return company data as JSON
        return response()->json($company);
    }
    
    /**
     * Get services for a company
     * 
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServices($companyId)
    {
        $company = Company::where('id', $companyId)->first();
        
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        
        $services = TreatmentType::where('company_id', $company->id)->get();
        
        return response()->json($services);
    }
    
    /**
     * Get doctors for a company
     * 
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoctors($companyId)
    {
        $company = Company::where('id', $companyId)->first();
        
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        
        $doctors = Doctor::where('company_id', $company->id)
            ->with('specialty')
            ->get();
        
        return response()->json($doctors);
    }
    
    /**
     * Submit contact form
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitContact(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'company_id' => 'required|string'
        ]);
        
        // Process contact submission (could be saved to DB or sent via email)
        // For now, we'll just return success
        
        return response()->json([
            'message' => 'Contact form submitted successfully',
            'data' => $validated
        ]);
    }
    
    /**
     * Book an appointment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookAppointment(Request $request)
    {
        // Validate appointment data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date' => 'required|date',
            'time' => 'required|string',
            'doctor_id' => 'required|string',
            'service_id' => 'nullable|string',
            'company_id' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        
        // Process appointment booking
        // For now, we'll just return success
        
        return response()->json([
            'message' => 'Appointment booked successfully',
            'data' => $validated
        ]);
    }
}
