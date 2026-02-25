<?php

namespace App\Traits;

trait DateFilterTrait 
{
    /**
     * Apply date filters to query safely
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param string $column The column to filter (e.g., 'created_at', 'clock_time')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateFilter($query, $request, $column = 'created_at')
    {
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            
            // Ensure we have both start and end dates
            if (count($dates) >= 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                
                // Validate dates are not empty
                if (!empty($startDate) && !empty($endDate)) {
                    $tableName = $query->getModel()->getTable();
                    $query = $query->whereRaw("$tableName.$column >= ?", [$startDate])
                        ->whereRaw("$tableName.$column <= ?", [$endDate]);
                }
            }
        }
        
        return $query;
    }
}