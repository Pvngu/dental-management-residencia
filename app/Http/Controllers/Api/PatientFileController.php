<?php

namespace App\Http\Controllers\Api;

use App\Models\State;
use App\Models\Signer;
use GuzzleHttp\Client;
use App\Classes\Common;
use App\Models\PatientFile;
use Illuminate\Support\Str;
use App\Models\PaymentSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ESignDocTemplate;
use Examyou\RestAPI\ApiResponse;
use GuzzleHttp\Exception\RequestException;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\PatientFile\PDFRequest;
use App\Http\Requests\Api\PatientFile\VoidRequest;
use App\Http\Requests\Api\PatientFile\IndexRequest;
use App\Http\Requests\Api\PatientFile\StoreRequest;
use App\Http\Requests\Api\PatientFile\DeleteRequest;
use App\Http\Requests\Api\PatientFile\UpdateRequest;
use App\Http\Requests\Api\PatientFile\ReminderRequest;
use App\Http\Requests\Api\PatientFile\SignEasyRequest;

class PatientFileController extends ApiBaseController
{
    protected $model = PatientFile::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $request = request();

        $query = $query->join('patients', 'patients.id', '=', 'patient_files.patient_id');

        if ($request->has('patient_id') && $request->patient_id != "") {
            $individualId = $this->getIdFromHash($request->patient_id);

            $query = $query->where('patient_files.patient_id', $individualId);

            if ($request->has('type') && $request->type != "") {
                $query = $query->where('patient_files.type', $request->type);
            }
        }

        return $query;
    }

    public function storing($PatientFile)
    {
        $loggedUser = user();

        if($loggedUser) {
            $PatientFile->created_by_id = $loggedUser->id;
        }
        
        return $PatientFile;
    }

    public function stats()
    {
        $company = company();
        
        $totalFiles = PatientFile::where('company_id', $company->id)->count();
        $totalSize = PatientFile::where('company_id', $company->id)->sum('file_size');
        
        // Count files by type
        $imageFiles = PatientFile::where('company_id', $company->id)
            ->where('file_type', 'like', 'image/%')
            ->count();
        
        $pdfFiles = PatientFile::where('company_id', $company->id)
            ->where('file_type', 'application/pdf')
            ->count();
        
        $docFiles = PatientFile::where('company_id', $company->id)
            ->whereIn('file_type', [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ])
            ->count();
        
        $otherFiles = $totalFiles - ($imageFiles + $pdfFiles + $docFiles);
        
        return ApiResponse::make('Success', [
            'totalFiles' => $totalFiles,
            'totalSize' => $totalSize,
            'imageFiles' => $imageFiles,
            'pdfFiles' => $pdfFiles,
            'docFiles' => $docFiles,
            'otherFiles' => $otherFiles,
        ]);
    }
}
