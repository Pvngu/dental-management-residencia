<?php

namespace App\Http\Controllers\Api;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Appointment;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Prescription\IndexRequest;
use App\Http\Requests\Api\Prescription\StoreRequest;
use App\Http\Requests\Api\Prescription\UpdateRequest;
use App\Http\Requests\Api\Prescription\DeleteRequest;
use App\Events\AppointmentUpdated;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Prescription::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Filter by patient
        if ($request->has('patient_id') && $request->patient_id != "") {
            $patientId = $this->getIdFromHash($request->patient_id);
            $query = $query->where('prescriptions.patient_id', $patientId);
        }

        // Filter by doctor
        if ($request->has('doctor_id') && $request->doctor_id != "") {
            $doctorId = $this->getIdFromHash($request->doctor_id);
            $query = $query->where('prescriptions.doctor_id', $doctorId);
        }

        // Filter by appointment
        if ($request->has('appointment_id') && $request->appointment_id != "") {
            $appointmentId = $this->getIdFromHash($request->appointment_id);
            $query = $query->where('prescriptions.appointment_id', $appointmentId);
        }

        // Filter by status
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('prescriptions.status', $request->status);
        }

        // Date filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('prescriptions.created_at >= ?', [$startDate])
                ->whereRaw('prescriptions.created_at <= ?', [$endDate]);
        }

        return $query;
    }

    public function storing($prescription)
    {
        $company = company();
        $prescription->company_id = $company->id;
        $prescription->prescription_date = now();
        
        return $prescription;
    }

    public function stored($prescription)
    {
        $request = request();
        
        // Store prescription items (medicines)
        if ($request->has('medicines') && is_array($request->medicines)) {
            foreach ($request->medicines as $medicine) {
                $prescriptionItem = new PrescriptionItem();
                $prescriptionItem->prescription_id = $prescription->id;
                
                // Always store medicine_name if provided
                if (!empty($medicine['medicine_name'])) {
                    $prescriptionItem->medicine_name = $medicine['medicine_name'];
                }
                
                // If medicine_id is provided, store it as well
                if (!empty($medicine['medicine_id'])) {
                    $medicineId = $this->getIdFromHash($medicine['medicine_id']);
                    $prescriptionItem->medicine_id = $medicineId;
                }
                
                $prescriptionItem->dosage = $medicine['dosage'];
                $prescriptionItem->frequency = $medicine['frequency'];
                $prescriptionItem->duration = $medicine['duration'];
                $prescriptionItem->instructions = $medicine['instructions'] ?? null;
                $prescriptionItem->company_id = $prescription->company_id;
                $prescriptionItem->save();
            }
        }

        // If prescription is linked to an appointment, broadcast update
        if ($prescription->appointment_id) {
            $appointment = Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription'])
                ->find($prescription->appointment_id);
            
            if ($appointment) {
                broadcast(new AppointmentUpdated($appointment, 'updated'))->toOthers();
            }
        }

        return $prescription;
    }

    public function updating($prescription)
    {
        return $prescription;
    }

    public function updated($prescription)
    {
        $request = request();
        
        // Update prescription items
        if ($request->has('medicines') && is_array($request->medicines)) {
            // Delete existing items
            PrescriptionItem::where('prescription_id', $prescription->id)->delete();
            
            // Add new items
            foreach ($request->medicines as $medicine) {
                $prescriptionItem = new PrescriptionItem();
                $prescriptionItem->prescription_id = $prescription->id;
                
                // Always store medicine_name if provided
                if (!empty($medicine['medicine_name'])) {
                    $prescriptionItem->medicine_name = $medicine['medicine_name'];
                }
                
                // If medicine_id is provided, store it as well
                if (!empty($medicine['medicine_id'])) {
                    $medicineId = $this->getIdFromHash($medicine['medicine_id']);
                    $prescriptionItem->medicine_id = $medicineId;
                }
                
                $prescriptionItem->dosage = $medicine['dosage'];
                $prescriptionItem->frequency = $medicine['frequency'];
                $prescriptionItem->duration = $medicine['duration'];
                $prescriptionItem->instructions = $medicine['instructions'] ?? null;
                $prescriptionItem->company_id = $prescription->company_id;
                $prescriptionItem->save();
            }
        }

        // If prescription is linked to an appointment, broadcast update
        if ($prescription->appointment_id) {
            $appointment = Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription'])
                ->find($prescription->appointment_id);
            
            if ($appointment) {
                broadcast(new AppointmentUpdated($appointment, 'updated'))->toOthers();
            }
        }

        return $prescription;
    }

    public function destroyed($prescription)
    {
        // If prescription was linked to an appointment, broadcast update after deletion
        // At this point, the prescription is already deleted, so we need to reload the appointment
        if ($prescription->appointment_id) {
            $appointment = Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription.prescriptionItems.medicine'])
                ->find($prescription->appointment_id);
            
            if ($appointment) {
                broadcast(new AppointmentUpdated($appointment, 'updated'))->toOthers();
            }
        }
    }

    public function download(Request $request, $id)
    {
        $prescriptionId = $this->getIdFromHash($id);
        
        // Get prescription with company scope
        $prescription = Prescription::with([
            'patient',
            'doctor.user',
            'appointment',
            'prescriptionItems.medicine'
        ])->find($prescriptionId);

        if (!$prescription) {
            abort(404, 'Prescription not found');
        }
        
        // Verify prescription belongs to current company
        $company = company();
        if ($prescription->company_id != $company->id) {
            abort(403, 'Unauthorized to access this prescription');
        }

        $pdf = Pdf::loadView('pdf.prescription', [
            'prescription' => $prescription,
            'company' => $company
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('prescription_' . $prescription->prescription_number . '.pdf');
    }

    public function getStats(Request $request)
    {
        $company = company();
        
        // Total prescriptions
        $totalPrescriptions = Prescription::where('company_id', $company->id)->count();
        
        // Active prescriptions
        $activePrescriptions = Prescription::where('company_id', $company->id)
            ->where('status', 'active')
            ->count();
        
        // Completed prescriptions
        $completedPrescriptions = Prescription::where('company_id', $company->id)
            ->where('status', 'completed')
            ->count();
        
        // Total prescriptions this month
        $prescriptionsThisMonth = Prescription::where('company_id', $company->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Most prescribed medicine
        $mostPrescribedMedicine = PrescriptionItem::join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->where('prescriptions.company_id', $company->id)
            ->whereNotNull('prescription_items.medicine_name')
            ->select('prescription_items.medicine_name', \DB::raw('COUNT(*) as prescription_count'))
            ->groupBy('prescription_items.medicine_name')
            ->orderBy('prescription_count', 'desc')
            ->first();
        
        // Most active doctor (prescribing)
        $mostActiveDoctor = Prescription::where('prescriptions.company_id', $company->id)
            ->join('doctors', 'prescriptions.doctor_id', '=', 'doctors.id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->select('users.name', \DB::raw('COUNT(*) as prescription_count'))
            ->groupBy('doctors.id', 'users.name')
            ->orderBy('prescription_count', 'desc')
            ->first();
        
        return response()->json([
            'totalPrescriptions' => $totalPrescriptions,
            'activePrescriptions' => $activePrescriptions,
            'completedPrescriptions' => $completedPrescriptions,
            'prescriptionsThisMonth' => $prescriptionsThisMonth,
            'mostPrescribedMedicine' => $mostPrescribedMedicine ? $mostPrescribedMedicine->medicine_name : '-',
            'mostActiveDoctor' => $mostActiveDoctor ? $mostActiveDoctor->name : '-',
        ]);
    }
}
