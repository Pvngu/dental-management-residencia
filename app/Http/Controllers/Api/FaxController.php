<?php

namespace App\Http\Controllers\Api;

use App\Models\Fax;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Classes\Files;

class FaxController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = Fax::class;

    protected $indexRequest = \App\Http\Requests\Api\Fax\IndexRequest::class;
    protected $storeRequest = \App\Http\Requests\Api\Fax\StoreRequest::class;
    protected $updateRequest = \App\Http\Requests\Api\Fax\UpdateRequest::class;
    protected $deleteRequest = \App\Http\Requests\Api\Fax\DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Search functionality
        if ($request->has('searchString') && $request->searchString != "") {
            $query = $query->where(function ($q) use ($request) {
                $q->where('to_number', 'like', '%' . $request->searchString . '%')
                  ->orWhere('from_number', 'like', '%' . $request->searchString . '%')
                  ->orWhere('file_name', 'like', '%' . $request->searchString . '%')
                  ->orWhere('notes', 'like', '%' . $request->searchString . '%');
            });
        }

        // Date filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('faxes.created_at >= ?', [$startDate])
                ->whereRaw('faxes.created_at <= ?', [$endDate]);
        }
        return $query->with([
            'patient' => function($q) {
                $q->select('id', 'company_id', 'user_id');
            },
            'patient.user' => function($q) {
                $q->select('id', 'name', 'last_name');
            },
            'insuranceProvider:id,name',
            'creator'
        ]);
    }

    public function storeFax(\App\Http\Requests\Api\Fax\StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $company = company();
            if (!$company) {
                throw new ApiException('Company not found');
            }

            $fax = new Fax();
            $fax->company_id = $company->id;
            $fax->patient_id = $request->patient_id ? $this->getIdFromHash($request->patient_id) : null;
            $fax->insurance_provider_id = $request->insurance_provider_id ? $this->getIdFromHash($request->insurance_provider_id) : null;
            $fax->to_number = $request->to_number;
            $fax->from_number = $request->from_number;
            $fax->direction = $request->direction;
            $fax->status = $request->status ?? 'queued';
            $fax->pages = $request->pages;
            $fax->provider_message_id = $request->provider_message_id;
            $fax->transmitted_at = $request->transmitted_at;
            $fax->error_message = $request->error_message;
            $fax->meta = $request->meta;
            $fax->notes = $request->notes;
            
            $user = auth()->user();
            if ($user) {
                $fax->created_by = $user->id;
            }

            // Handle file upload
            if ($request->hasFile('file')) {
                $path = Files::uploadLocalOrS3($request->file('file'), 'faxes');
                $fax->file = $path;
                $fax->file_name = $request->file('file')->getClientOriginalName();
            }

            $fax->save();

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $fax->xid
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed to create fax. ' . $e->getMessage());
        }
    }

    public function updateFax(\App\Http\Requests\Api\Fax\UpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $xid = $id;
            $id = $this->getIdFromHash($xid);
            $fax = Fax::find($id);
            
            if (!$fax) {
                throw new ApiException('Fax not found');
            }

            $fax->patient_id = $request->patient_id ? $this->getIdFromHash($request->patient_id) : null;
            $fax->insurance_provider_id = $request->insurance_provider_id ? $this->getIdFromHash($request->insurance_provider_id) : null;
            $fax->to_number = $request->to_number;
            $fax->from_number = $request->from_number;
            $fax->direction = $request->direction;
            $fax->status = $request->status ?? $fax->status;
            $fax->pages = $request->pages;
            $fax->provider_message_id = $request->provider_message_id;
            $fax->transmitted_at = $request->transmitted_at;
            $fax->error_message = $request->error_message;
            $fax->meta = $request->meta;
            $fax->notes = $request->notes;

            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($fax->file) {
                    if (config('filesystems.default') === 's3') {
                        Storage::disk('s3')->delete($fax->file);
                    } else {
                        if (Storage::exists($fax->file)) {
                            Storage::delete($fax->file);
                        }
                    }
                }

                $path = Files::uploadLocalOrS3($request->file('file'), 'faxes');
                $fax->file = $path;
                $fax->file_name = $request->file('file')->getClientOriginalName();
            }

            $fax->save();

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $fax->xid
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed to update fax. ' . $e->getMessage());
        }
    }

    public function storing($fax)
    {
        $company = company();
        
        if (!$company) {
            throw new ApiException('Company not found');
        }
        
        // Set company_id
        $fax->company_id = $company->id;

        // Set created_by
        $user = auth()->user();
        if ($user) {
            $fax->created_by = $user->id;
        }

        // Set default status if not provided
        if (!$fax->status) {
            $fax->status = 'queued';
        }

        return $fax;
    }

    public function stored($fax)
    {
        $request = request();
        
        // Handle file upload
        if ($request->hasFile('file')) {
            $path = Files::uploadLocalOrS3($request->file('file'), 'faxes');
            
            $fax->file = $path;
            $fax->file_name = $request->file('file')->getClientOriginalName();
            $fax->save();
        }

        // TODO: Dispatch job to send fax via provider (Phaxio, InterFAX, etc.)
        // dispatch(new SendFaxJob($fax));

        return $fax;
    }

    public function updating($fax)
    {
        $request = request();
        
        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($fax->file && Storage::exists($fax->file)) {
                Storage::delete($fax->file);
            }

            $path = Files::uploadLocalOrS3($request->file('file'), 'faxes');
            
            $fax->file = $path;
            $fax->file_name = $request->file('file')->getClientOriginalName();
        }

        return $fax;
    }

    public function destroying($fax)
    {
        // Delete associated file
        if ($fax->file) {
            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->delete($fax->file);
            } else {
                Storage::delete($fax->file);
            }
        }

        return $fax;
    }

    /**
     * Resend fax
     */
    public function resend($id)
    {
        $fax = Fax::find($id);

        if (!$fax) {
            return ApiResponse::make('Fax not found', [], 404);
        }

        // Update status to queued
        $fax->status = 'queued';
        $fax->error_message = null;
        $fax->save();

        // TODO: Dispatch job to resend fax
        // dispatch(new SendFaxJob($fax));

        return ApiResponse::make('Fax queued for resending', $fax);
    }

    /**
     * Webhook endpoint for fax provider callbacks
     */
    public function webhook()
    {
        $request = request();

        // TODO: Implement provider-specific webhook handling
        // This will vary based on the fax provider (Phaxio, InterFAX, etc.)
        // Example:
        // - Validate webhook signature
        // - Extract fax data from payload
        // - Update fax status
        // - Store received file
        // - Send notifications

        return ApiResponse::make('Webhook received', []);
    }
}
