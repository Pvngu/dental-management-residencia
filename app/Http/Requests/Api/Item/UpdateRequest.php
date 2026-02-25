<?php
namespace App\Http\Requests\Api\Item;

use App\Classes\Common;
use App\Rules\ValidForeignKey;
use Illuminate\Validation\Rule;
use App\Http\Requests\Api\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'name')
                    ->where(fn ($query) => $query->where('company_id', Company()->id))
                    ->ignore(Common::getIdFromHash($this->route('item'))),
            ],
            'category_id' => [
                'nullable',
                'string',
                new ValidForeignKey('item_categories'),
            ],
            'unit' => [
                'required',
                Rule::in(['box','pcs','dozen','kg','g','mg','lb','oz','m','cm','mm','in','ft','km','ml']),
            ],
            'item_length' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $length = request()->item_length;
                    $width = request()->item_width;
                    $height = request()->item_height;
                    $present = array_filter([$length, $width, $height], function ($v) {
                        return !is_null($v) && $v !== '';
                    });
                    if (count($present) > 0 && (is_null($length) || is_null($width) || is_null($height) || $length === '' || $width === '' || $height === '')) {
                        $fail('All of item_length, item_width, and item_height must be present if any is provided.');
                    }
                },
            ],
            'item_width' => 'nullable|numeric',
            'item_height' => 'nullable|numeric',

            'dimension_unit' => [
                'nullable',
                Rule::in(['cm', 'in']),
            ],
            'weight' => 'nullable|numeric',
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('items', 'sku')
                    ->where(fn ($query) => $query->where('company_id', Company()->id))
                    ->ignore(Common::getIdFromHash($this->route('item'))),
            ],
            'brand_id' => [
                'nullable',
                'string',
                new ValidForeignKey('item_brands'),
            ],
            'manufacturer_id' => [
                'nullable',
                'string',
                new ValidForeignKey('item_manufactures'),
            ],
            'description' => 'nullable|string',
            'available_quantity' => 'required|integer|min:0',
            // Sales information
            'is_sellable' => 'nullable|boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_description' => 'nullable|string',
            // Purchase information
            'is_purchasable' => 'nullable|boolean',
            'cost_price' => 'nullable|numeric|min:0',
            'purchase_description' => 'nullable|string',
            'supplier_id' => [
                'nullable',
                'string',
                new ValidForeignKey('suppliers'),
            ],
        ];
    }
}
