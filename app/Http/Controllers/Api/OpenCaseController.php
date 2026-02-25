<?php

namespace App\Http\Controllers\Api;

use App\Models\OpenCase;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\OpenCase\StoreRequest;
use App\Http\Requests\Api\OpenCase\UpdateRequest;
use App\Http\Requests\Api\OpenCase\DeleteRequest;
use App\Http\Requests\Api\OpenCase\IndexRequest;

class OpenCaseController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = OpenCase::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;
    /**
     * Get active (open or in_progress) cases for a specific patient
     */
    public function getActiveCases($patientId)
    {
        try {
            $patient_id = \Vinkla\Hashids\Facades\Hashids::decode($patientId)[0] ?? null;
            
            $cases = OpenCase::where('patient_id', $patient_id)
                ->whereIn('status', ['open', 'in_progress'])
                ->with(['patient.user', 'histories.user'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $cases,
                'count' => $cases->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
