<?php

namespace App\Http\Controllers\Api;

use App\Models\ZipCode;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use Illuminate\Http\Request;

class ZipCodeController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = ZipCode::class;

    public function modifyIndex($query)
    {
        $request = request();
        return $query;
    }

    /**
     * Get all zip codes for dropdown options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allOptions()
    {
        $zipCodes = ZipCode::select('id', 'zip_code', 'city', 'state_code')
            ->orderBy('zip_code')
            ->get()
            ->map(function ($zipCode) {
                return [
                    'id' => $zipCode->id,
                    'xid' => $zipCode->xid,
                    'label' => $zipCode->zip_code . ' - ' . $zipCode->city . ', ' . $zipCode->state_code,
                    'zip_code' => $zipCode->zip_code,
                    'city' => $zipCode->city,
                    'state_code' => $zipCode->state_code,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $zipCodes
        ]);
    }

    /**
     * Search zip codes by query
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $zipCodes = ZipCode::where('zip_code', 'like', $query . '%')
            ->orWhere('city', 'like', '%' . $query . '%')
            ->select('id', 'zip_code', 'city', 'state_code')
            ->orderBy('zip_code')
            ->limit(50)
            ->get()
            ->map(function ($zipCode) {
                return [
                    'id' => $zipCode->id,
                    'xid' => $zipCode->xid,
                    'label' => $zipCode->zip_code . ' - ' . $zipCode->city . ', ' . $zipCode->state_code,
                    'zip_code' => $zipCode->zip_code,
                    'city' => $zipCode->city,
                    'state_code' => $zipCode->state_code,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $zipCodes
        ]);
    }

    /**
     * Find nearby zip codes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function nearby(Request $request)
    {
        $zipCode = $request->input('zip_code');
        $radius = $request->input('radius', 10); // Default 10 miles

        if (!$zipCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zip code is required'
            ], 422);
        }

        $sourceZip = ZipCode::where('zip_code', $zipCode)->first();

        if (!$sourceZip) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zip code not found'
            ], 404);
        }

        // Simple nearby search (this could be enhanced with actual distance calculation)
        $nearbyZips = ZipCode::where('state_code', $sourceZip->state_code)
            ->where('zip_code', '!=', $zipCode)
            ->select('id', 'zip_code', 'city', 'state_code')
            ->orderBy('zip_code')
            ->limit(50)
            ->get()
            ->map(function ($zipCode) {
                return [
                    'id' => $zipCode->id,
                    'xid' => $zipCode->xid,
                    'label' => $zipCode->zip_code . ' - ' . $zipCode->city . ', ' . $zipCode->state_code,
                    'zip_code' => $zipCode->zip_code,
                    'city' => $zipCode->city,
                    'state_code' => $zipCode->state_code,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $nearbyZips
        ]);
    }
}
