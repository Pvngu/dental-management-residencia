<?php

namespace App\Http\Controllers\Api;

use App\Models\DentalTreatMonitor;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use Illuminate\Http\Request;
use App\Http\Requests\Api\DentalTreatMonitor\IndexRequest;
use App\Http\Requests\Api\DentalTreatMonitor\StoreRequest;
use App\Http\Requests\Api\DentalTreatMonitor\UpdateRequest;
use App\Http\Requests\Api\DentalTreatMonitor\DeleteRequest;

class DentalTreatMonitorController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = DentalTreatMonitor::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Filter by patient if provided
        if ($request->has('patient_id') && $request->patient_id != "") {
            $query = $query->where('patient_id', $this->getIdFromHash($request->patient_id));
        }

        // Filter by tooth number if provided
        if ($request->has('tooth_number') && $request->tooth_number != "") {
            $query = $query->where('tooth_number', $request->tooth_number);
        }

        // Filter by type if provided
        if ($request->has('type') && $request->type != "") {
            $query = $query->where('type', $request->type);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('status', $request->status);
        }

        // Include relationships
        $query = $query->with(['patient:id,first_name,last_name', 'creator:id,name', 'resolver:id,name']);

        return $query;
    }

    /**
     * Resolve a treat and monitor item
     */
    public function resolve(Request $request, $id)
    {
        try {
            $treatMonitor = DentalTreatMonitor::findOrFail($this->getIdFromHash($id));
            
            $treatMonitor->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $this->getUserIdFromToken(),
                'updated_by' => $this->getUserIdFromToken(),
            ]);

            return $this->respondWithSuccess("Treat and monitor item resolved successfully", $treatMonitor);
        } catch (\Exception $e) {
            return $this->respondWithError("Error resolving treat and monitor item", $e->getMessage());
        }
    }

    /**
     * Reactivate a treat and monitor item
     */
    public function reactivate(Request $request, $id)
    {
        try {
            $treatMonitor = DentalTreatMonitor::findOrFail($this->getIdFromHash($id));
            
            $treatMonitor->update([
                'status' => 'active',
                'resolved_at' => null,
                'resolved_by' => null,
                'updated_by' => $this->getUserIdFromToken(),
            ]);

            return $this->respondWithSuccess("Treat and monitor item reactivated successfully", $treatMonitor);
        } catch (\Exception $e) {
            return $this->respondWithError("Error reactivating treat and monitor item", $e->getMessage());
        }
    }

    /**
     * Hook called when storing a new treat monitor item
     */
    public function storing($treatMonitor)
    {
        $treatMonitor->created_by = User()->id;
        
        // Set the patient_id from hash if provided
        $request = request();
        if ($request->has('x_patient_id')) {
            $treatMonitor->patient_id = $this->getIdFromHash($request->x_patient_id);
        }

        return $treatMonitor;
    }

    /**
     * Hook called when updating a treat monitor item
     */
    public function updating($treatMonitor)
    {
        $treatMonitor->updated_by = User()->id;
        
        // Set the patient_id from hash if provided
        $request = request();
        if ($request->has('x_patient_id')) {
            $treatMonitor->patient_id = $this->getIdFromHash($request->x_patient_id);
        }

        return $treatMonitor;
    }
}
