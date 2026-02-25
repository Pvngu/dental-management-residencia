<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorDepartment;
use App\Http\Controllers\ApiBaseController;
use App\Traits\SelectOptionsTraits;
use App\Traits\CompanyTraits;

class DoctorDepartmentController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;
    protected $model = DoctorDepartment::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('doctor_departments.created_at >= ?', [$startDate])
                ->whereRaw('doctor_departments.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
