<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\OrderMedicine;
use App\Traits\CompanyTraits;
use App\Models\PurchaseMedicine;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\PurchaseMedicine\IndexRequest;
use App\Http\Requests\Api\PurchaseMedicine\StoreRequest;
use App\Http\Requests\Api\PurchaseMedicine\DeleteRequest;
use App\Http\Requests\Api\PurchaseMedicine\UpdateRequest;

class PurchaseMedicineController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = PurchaseMedicine::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        return $query;
    }

    public function storeMedicine(StoreRequest $request)
    {
        $company = company();

        try {
            DB::beginTransaction();

            $purchaseMedicine = new PurchaseMedicine();
            $purchaseMedicine->reference_no = $request->reference_no;
            $purchaseMedicine->delivery_date = \Carbon\Carbon::parse($request->delivery_date)->format('Y-m-d H:i:s');
            $purchaseMedicine->payment_type = $request->payment_type;
            $purchaseMedicine->payment_status = $request->payment_status;
            $purchaseMedicine->subtotal = $request->subtotal;
            $purchaseMedicine->discount = $request->discount;
            $purchaseMedicine->tax = $request->tax;
            $purchaseMedicine->adjustments = $request->adjustments;
            $purchaseMedicine->total = $request->total;
            $purchaseMedicine->note = $request->note;
            $purchaseMedicine->company_id = $company->id;
            $purchaseMedicine->save();

            // Save order medicines
            if ($request->has('order_medicines') && count($request->order_medicines) > 0) {
                foreach ($request->order_medicines as $medicine) {
                    $orderMedicine = new OrderMedicine();
                    $orderMedicine->purchase_medicine_id = $purchaseMedicine->id;
                    $orderMedicine->medicine_id = $this->getIdFromHash($medicine['x_medicine_id']);
                    $orderMedicine->lot_no = $medicine['lot_no'];
                    $orderMedicine->expiry_date = \Carbon\Carbon::parse($medicine['expiry_date'])->format('Y-m-d H:i:s');
                    $orderMedicine->quantity = $medicine['quantity'];
                    $orderMedicine->rate = $medicine['rate'];
                    $orderMedicine->amount = $medicine['amount'];
                    $orderMedicine->company_id = $company->id;
                    $orderMedicine->save();
                }
            }

            DB::commit();
            
            return ApiResponse::make('Success', [
                'xid' => $purchaseMedicine->xid
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApiException($e->getMessage());
        }
    }

    public function updateMedicine(UpdateRequest $request)
    {
        $company = company();

        try {
            DB::beginTransaction();

            $purchaseMedicine = PurchaseMedicine::findOrFail($this->getIdFromHash($request->xid));
            $purchaseMedicine->reference_no = $request->reference_no;
            $purchaseMedicine->delivery_date = \Carbon\Carbon::parse($request->delivery_date)->format('Y-m-d H:i:s');
            $purchaseMedicine->payment_type = $request->payment_type;
            $purchaseMedicine->payment_status = $request->payment_status;
            $purchaseMedicine->subtotal = $request->subtotal;
            $purchaseMedicine->discount = $request->discount;
            $purchaseMedicine->tax = $request->tax;
            $purchaseMedicine->adjustments = $request->adjustments;
            $purchaseMedicine->total = $request->total;
            $purchaseMedicine->note = $request->note;
            $purchaseMedicine->save();

            // Delete existing order medicines
            OrderMedicine::where('purchase_medicine_id', $purchaseMedicine->id)->delete();

            // Save new order medicines
            if ($request->has('order_medicines') && count($request->order_medicines) > 0) {
                foreach ($request->order_medicines as $medicine) {
                    $orderMedicine = new OrderMedicine();
                    $orderMedicine->purchase_medicine_id = $purchaseMedicine->id;
                    $orderMedicine->medicine_id = $this->getIdFromHash($medicine['x_medicine_id']);
                    $orderMedicine->lot_no = $medicine['lot_no'];
                    $orderMedicine->expiry_date = \Carbon\Carbon::parse($medicine['expiry_date'])->format('Y-m-d H:i:s');
                    $orderMedicine->quantity = $medicine['quantity'];
                    $orderMedicine->rate = $medicine['rate'];
                    $orderMedicine->amount = $medicine['amount'];
                    $orderMedicine->company_id = $company->id;
                    $orderMedicine->save();
                }
            }

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $purchaseMedicine->xid
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApiException($e->getMessage());
        }
    }
}