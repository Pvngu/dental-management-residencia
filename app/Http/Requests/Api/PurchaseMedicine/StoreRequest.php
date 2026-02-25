<?php

namespace App\Http\Requests\Api\PurchaseMedicine;

use App\Http\Requests\Api\BaseRequest;
use App\Models\OrderMedicine;
use Illuminate\Validation\Validator;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reference_no' => 'required|string|max:191',
            'delivery_date' => 'required|date',
            'payment_type' => 'required|string|in:cash,cheque,bank_transfer,credit_card',
            'payment_status' => 'required|string|in:paid,unpaid,partial',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'adjustments' => 'nullable|numeric',
            'total' => 'required|numeric',
            'note' => 'nullable|string',
            'order_medicines' => 'required|array|min:1',
            'order_medicines.*.x_medicine_id' => 'required|string',
            'order_medicines.*.lot_no' => 'required|string|max:191',
            'order_medicines.*.expiry_date' => 'required|date',
            'order_medicines.*.quantity' => 'required|integer|min:1',
            'order_medicines.*.rate' => 'required|numeric|min:0',
            'order_medicines.*.amount' => 'required|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $company = company();
            $orderMedicines = $this->input('order_medicines', []);

            foreach ($orderMedicines as $index => $medicine) {
                $medicineId = $this->getIdFromHash($medicine['x_medicine_id'] ?? null);
                $lotNo = $medicine['lot_no'] ?? null;

                if ($medicineId && $lotNo) {
                    // Check if this batch already exists
                    $exists = OrderMedicine::where('medicine_id', $medicineId)
                        ->where('lot_no', $lotNo)
                        ->where('company_id', $company->id)
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add(
                            'order_medicines',
                            "Batch number '{$lotNo}' already exists for this medicine at row " . ($index + 1) . ". Please use a different batch number or update the existing batch."
                        );
                    }
                }
            }
        });
    }

    private function getIdFromHash($xid)
    {
        if (!$xid) {
            return null;
        }

        try {
            return \Vinkla\Hashids\Facades\Hashids::decode($xid)[0] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}