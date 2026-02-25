<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Country\IndexRequest;
use App\Http\Requests\Api\Country\StoreRequest;
use App\Http\Requests\Api\Country\UpdateRequest;
use App\Http\Requests\Api\Country\DeleteRequest;
use Illuminate\Support\Facades\DB;

class CountryController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = Country::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Search by country name or code
        if ($request->has('search') && $request->search != "") {
            $search = $request->search;
            $query = $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('code', 'LIKE', '%' . $search . '%');
            });
        }

        // Include related data with counts
        $query = $query->with(['states' => function($query) {
            $query->select('id', 'country_id')->limit(1); // Just for count
        }]);

        return $query;
    }

    public function allOptions()
    {
        $countries = Country::where('status', 1)
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'data' => $countries,
            'success' => true
        ]);
    }

    /**
     * Get countries with state and zip code counts
     */
    public function withCounts()
    {
        $countries = Country::where('status', 1)
            ->withCount(['states', 'zipCodes'])
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'data' => $countries,
            'success' => true
        ]);
    }

    /**
     * Get geographic coverage statistics
     */
    public function statistics()
    {
        $stats = [
            'total_countries' => Country::where('status', 1)->count(),
            'total_states' => DB::table('states')
                ->join('countries', 'states.country_id', '=', 'countries.id')
                ->where('countries.status', 1)
                ->where('states.status', 1)
                ->count(),
            'total_zip_codes' => DB::table('zip_codes')
                ->join('states', 'zip_codes.state_id', '=', 'states.id')
                ->join('countries', 'states.country_id', '=', 'countries.id')
                ->where('countries.status', 1)
                ->where('states.status', 1)
                ->where('zip_codes.status', 1)
                ->count(),
            'countries_with_coordinates' => DB::table('zip_codes')
                ->join('states', 'zip_codes.state_id', '=', 'states.id')
                ->join('countries', 'states.country_id', '=', 'countries.id')
                ->where('countries.status', 1)
                ->where('zip_codes.latitude', '!=', null)
                ->where('zip_codes.longitude', '!=', null)
                ->distinct('countries.id')
                ->count('countries.id'),
            'by_country' => Country::where('status', 1)
                ->withCount(['states', 'zipCodes'])
                ->orderBy('name')
                ->get()
        ];
        
        return response()->json([
            'data' => $stats,
            'success' => true
        ]);
    }
}
