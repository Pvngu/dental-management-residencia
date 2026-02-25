<?php

namespace App\Http\Requests\Api\Expense;

use App\Rules\ValidForeignKey;
use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric',
            'category_id' => [
                'required',
                'string',
                new ValidForeignKey('expense_categories'),
            ],
            'expense_for' => 'required|string|max:255',
            'payment_type' => 'required|in:cash,card,bank,other',
            'reference_number' => 'nullable|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
