<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientCreditCard;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\PatientCreditCard\StoreRequest;
use App\Http\Requests\Api\PatientCreditCard\UpdateRequest;
use App\Http\Requests\Api\PatientCreditCard\DeleteRequest;
use App\Http\Requests\Api\PatientCreditCard\IndexRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientCreditCardController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = PatientCreditCard::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Filter by patient if provided
        if ($request->has('patient_id') && $request->patient_id != '') {
            $patientId = $this->getIdFromHash($request->patient_id);
            if ($patientId) {
                $query = $query->where('patient_id', $patientId);
            }
        }

        return $query;
    }

    public function storing($data)
    {
        $company = company();
        
        // Add company_id and convert patient_id from hash
        $data['company_id'] = $company->id;
        $patientId = $this->getIdFromHash($data['patient_id']);
        
        if (!$patientId) {
            throw new \InvalidArgumentException('Invalid patient ID provided.');
        }
        
        $data['patient_id'] = $patientId;

        // Validate expiry date is not in the past
        if (isset($data['expiry_month']) && isset($data['expiry_year'])) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            if ($data['expiry_year'] < $currentYear || 
                ($data['expiry_year'] == $currentYear && $data['expiry_month'] < $currentMonth)) {
                throw new ApiException('The card expiration date cannot be in the past.');
            }
            
            // Format exp_date as MM/YY
            $data['exp_date'] = $data['expiry_month'] . '/' . substr($data['expiry_year'], -2);
            
            // Remove individual fields as we store exp_date
            unset($data['expiry_month']);
            unset($data['expiry_year']);
        }

        // If this is the first card for the patient, make it default
        $existingCards = PatientCreditCard::where('patient_id', $data['patient_id'])->count();
        if ($existingCards === 0) {
            $data['is_default'] = true;
        }

        return $data;
    }

    public function updating($model, $data)
    {
        // Handle exp_date format (MM/YY) if provided
        if (isset($data['exp_date'])) {
            if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $data['exp_date'], $matches)) {
                $data['exp_month'] = $matches[1];
                $data['exp_year'] = '20' . $matches[2]; // Convert YY to YYYY
                unset($data['exp_date']);
            }
        }

        // Validate expiry date is not in the past
        if (isset($data['exp_month']) && isset($data['exp_year'])) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            if ($data['exp_year'] < $currentYear || 
                ($data['exp_year'] == $currentYear && $data['exp_month'] < $currentMonth)) {
                throw new ApiException('The card expiration date cannot be in the past.');
            }
        }

        // If setting as default, unset other cards
        if (isset($data['is_default']) && $data['is_default'] === true) {
            PatientCreditCard::where('patient_id', $model->patient_id)
                ->where('id', '!=', $model->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }

    public function updateCard(Request $request, $patientId, $cardId)
    {
        $patientIdDecoded = $this->getIdFromHash($patientId);
        $cardIdDecoded = $this->getIdFromHash($cardId);

        if (!$patientIdDecoded || !$cardIdDecoded) {
            throw new ApiException('Invalid patient ID or card ID provided.');
        }

        $creditCard = PatientCreditCard::where('id', $cardIdDecoded)
            ->where('patient_id', $patientIdDecoded)
            ->firstOrFail();

        // Validate the request
        $updateRequest = app(UpdateRequest::class);
        $validated = $updateRequest->validated();

        // Process the data using the updating method
        $processedData = $this->updating($creditCard, $validated);

        // Update the card
        $creditCard->update($processedData);

        return ApiResponse::make(trans('patient_credit_card.updated'), $creditCard);
    }

    public function setAsDefault(Request $request, $patientId, $cardId)
    {
        $patientIdDecoded = $this->getIdFromHash($patientId);
        $cardIdDecoded = $this->getIdFromHash($cardId);

        if (!$patientIdDecoded || !$cardIdDecoded) {
            throw new ApiException('Invalid patient ID or card ID provided.');
        }

        $creditCard = PatientCreditCard::where('id', $cardIdDecoded)
            ->where('patient_id', $patientIdDecoded)
            ->firstOrFail();

        $creditCard->setAsDefault();

        return ApiResponse::make(trans('patient_credit_card.set_as_default'), $creditCard);
    }

    public function destroying($model)
    {
        // If this was the default card, set another card as default
        if ($model->is_default) {
            $nextCard = PatientCreditCard::where('patient_id', $model->patient_id)
                ->where('id', '!=', $model->id)
                ->first();
            
            if ($nextCard) {
                $nextCard->setAsDefault();
            }
        }

        return $model;
    }
}
