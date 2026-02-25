<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\ItemBrand;
use App\Models\ItemCategory;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Promotion\IndexRequest;
use App\Http\Requests\Api\Promotion\StoreRequest;
use App\Http\Requests\Api\Promotion\UpdateRequest;
use App\Http\Requests\Api\Promotion\DeleteRequest;
use Illuminate\Http\Request;

class PromotionController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Promotion::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();
        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];
            $query = $query->whereRaw('promotions.start_date >= ?', [$startDate])
                ->whereRaw('promotions.end_date <= ?', [$endDate]);
        }

        return $query;
    }

    public function storing(Promotion $promotion)
    {

        $actualRequest = request();
        $filteredData = $actualRequest->except(['promotion_targets']);
        $promotion->fill($filteredData);

        $promotion->company_id = company()->id;
        // $promotion->created_by = user()->id;
        // $promotion->updated_by = user()->id;
        
        return $promotion;
    }
    
    /**
     * This method is called after the promotion is stored
     */
    /**
     * This method is called after the promotion is stored
     */
    public function stored(Promotion $promotion)
    {
        // Get the request data
        $actualRequest = request();
        
        // Create promotion targets if they exist in the request
        if ($actualRequest->has('promotion_targets') && is_array($actualRequest->promotion_targets)) {
            foreach ($actualRequest->promotion_targets as $target) {
                $promotionTarget = new PromotionTarget();
                $promotionTarget->promotion_id = $promotion->id;
                $promotionTarget->target_type = $target['target_type'];
                $promotionTarget->target_id = $this->getIdFromHash($target['target_id']);
                $promotionTarget->company_id = company()->id; 
                // $promotionTarget->created_by = user()->id;
                // $promotionTarget->updated_by = user()->id;
                $promotionTarget->save();
            }
        }
    }

    public function updating(UpdateRequest $request, $id)
    {
        $promotion = Promotion::findOrFail($this->getIdFromHash($id));
        $requestData = $request->validated();

        $promotion->name = $requestData['name'];
        $promotion->discount_type = $requestData['discount_type'];
        $promotion->discount_value = $requestData['discount_value'];
        $promotion->start_date = $requestData['start_date'];
        $promotion->end_date = $requestData['end_date'];
        $promotion->is_active = $requestData['is_active'];
        $promotion->save();

        // Update promotion targets - first delete existing ones
        PromotionTarget::where('promotion_id', $promotion->id)->delete();

        // Create new targets
        if (isset($requestData['promotion_targets']) && count($requestData['promotion_targets']) > 0) {
            foreach ($requestData['promotion_targets'] as $target) {
                $promotionTarget = new PromotionTarget();
                $promotionTarget->promotion_id = $promotion->id;
                $promotionTarget->target_type = $target['target_type'];
                $promotionTarget->target_id = $this->getIdFromHash($target['target_id']);
                $promotionTarget->company_id = company()->id;
                $promotionTarget->created_by = user()->id;
                $promotionTarget->updated_by = user()->id;
                $promotionTarget->save();
            }
        }

        return response()->json([
            'xid' => $promotion->xid,
            'message' => __('promotions.updated')
        ]);
    }
}
