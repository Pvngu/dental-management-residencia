<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Item;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Sale\IndexRequest;
use App\Http\Requests\Api\Sale\StoreRequest;
use App\Http\Requests\Api\Sale\UpdateRequest;
use App\Http\Requests\Api\Sale\DeleteRequest;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Sale::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('sales.sold_at >= ?', [$startDate])
                ->whereRaw('sales.sold_at <= ?', [$endDate]);
        }

        return $query;
    }

    public function storing(Sale $sale)
    {
        $request = request();
        $company = company();
        
        // Set company_id (this is crucial for proper operation)
        $sale->company_id = $company->id;

        // Auto-generate sale_number if not provided
        if (empty($request->sale_number)) {
            $sale->sale_number = $this->generateSaleNumber();
        }

        // Auto-associate appointment if patient has an appointment today
        if (!empty($request->patient_id) && empty($request->appointment_id)) {
            $patientId = $this->getIdFromHash($request->patient_id);
            if ($patientId) {
                $todayAppointment = \App\Models\Appointment::where('patient_id', $patientId)
                    ->where('company_id', $company->id)
                    ->whereDate('appointment_date', now()->toDateString())
                    ->whereIn('status', ['confirmed', 'checked_in', 'in_progress'])
                    ->orderBy('appointment_date', 'desc')
                    ->first();
                
                if ($todayAppointment) {
                    $sale->appointment_id = $todayAppointment->id;
                }
            }
        }

        return $sale;
    }

    public function stored(Sale $sale)
    {
        $request = request();
        $company = company();

        // Handle sale details if provided
        if ($request->has('sale_details') && is_array($request->sale_details)) {
            foreach ($request->sale_details as $detail) {
                $saleDetail = new \App\Models\SaleDetail();
                $saleDetail->fill($detail);
                $saleDetail->sale_id = $sale->id;
                $saleDetail->company_id = $company->id;
                $saleDetail->save();

                // Only update inventory if the sale is not a draft
                if ($sale->status !== 'draft' && !empty($detail['item_id'])) {
                    $this->updateInventory($detail['item_id'], $detail['quantity'] ?? 1);
                }
            }
        }

        return $sale;
    }

    public function updating(Sale $sale)
    {
        $request = request();
        
        // Check if status is changing from draft to paid/pending
        $oldStatus = $sale->status;
        $newStatus = $request->status ?? $sale->status;
        
        // Store the status change information for use in updated()
        $sale->_status_changed = ($oldStatus === 'draft' && $newStatus !== 'draft');
        
        return $sale;
    }

    public function updated(Sale $sale)
    {
        $request = request();
        $company = company();
        
        // If status changed from draft to completed, update inventory
        if (isset($sale->_status_changed) && $sale->_status_changed) {
            // Get all sale details and update inventory
            $saleDetails = $sale->details;
            foreach ($saleDetails as $detail) {
                if (!empty($detail->item_id)) {
                    $this->updateInventory($detail->x_item_id, $detail->quantity ?? 1);
                }
            }
        }

        // Handle sale details updates if provided
        if ($request->has('sale_details') && is_array($request->sale_details)) {
            // Delete existing details
            \App\Models\SaleDetail::where('sale_id', $sale->id)->delete();
            
            // Add new details
            foreach ($request->sale_details as $detail) {
                $saleDetail = new \App\Models\SaleDetail();
                $saleDetail->fill($detail);
                $saleDetail->sale_id = $sale->id;
                $saleDetail->company_id = $company->id;
                $saleDetail->save();
            }
        }

        return $sale;
    }

    /**
     * Update inventory for sold items
     */
    private function updateInventory($itemId, $quantity)
    {
        try {
            // Decode the hash ID to get the real ID
            $realItemId = $this->getIdFromHash($itemId);
            if (!$realItemId) {
                Log::warning("Invalid hash ID for inventory update: {$itemId}");
                return;
            }
            
            // Find the item by real ID
            $item = Item::find($realItemId);
            
            if (!$item) {
                Log::warning("Item not found for inventory update. Hash ID: {$itemId}, Real ID: {$realItemId}");
                return;
            }

            // Only update inventory for goods, not services
            if ($item->type !== 'goods') {
                Log::info("Item {$item->name} is a service, no inventory update needed");
                return;
            }

            // Check if there's enough inventory
            if ($item->available_quantity < $quantity) {
                Log::warning("Insufficient inventory for item {$item->name}. Available: {$item->available_quantity}, Requested: {$quantity}");
                // You can decide whether to throw an exception or just log the warning
                // For now, we'll just log and continue
                return;
            }

            // Update the inventory
            $item->available_quantity -= $quantity;
            $item->save();

            Log::info("Inventory updated for item {$item->name}. Quantity reduced by {$quantity}. New quantity: {$item->available_quantity}");

        } catch (\Exception $e) {
            Log::error("Error updating inventory for item {$itemId}: " . $e->getMessage());
        }
    }
    /**
     * Generate a unique sale number
     */
    private function generateSaleNumber()
    {
        $company = company();
        if (!$company) {
            throw new \Exception('Company not found');
        }
        
        $companyId = $company->id;
        $year = date('Y');
        $month = date('m');
        $prefix = "S-{$year}{$month}-";
        
        // Use database transaction to prevent race conditions
        return DB::transaction(function () use ($companyId, $prefix, $year, $month) {
            // Get the highest sequential number for this company and month using SQL
            $prefixLength = strlen($prefix);
            $maxNumber = DB::table('sales')
                ->where('company_id', $companyId)
                ->where('sale_number', 'LIKE', "{$prefix}%")
                ->lockForUpdate()
                ->selectRaw("MAX(CAST(SUBSTRING(sale_number, {$prefixLength} + 1) AS UNSIGNED)) as max_number")
                ->value('max_number');
            
            $nextNumber = ($maxNumber ?? 0) + 1;
            
            // Generate the new sale number
            $newSaleNumber = sprintf("%s%04d", $prefix, $nextNumber);
            
            // Double-check uniqueness (extra safety)
            $attempts = 0;
            while (DB::table('sales')
                ->where('company_id', $companyId)
                ->where('sale_number', $newSaleNumber)
                ->exists() && $attempts < 10) {
                $nextNumber++;
                $newSaleNumber = sprintf("%s%04d", $prefix, $nextNumber);
                $attempts++;
            }
            
            if ($attempts >= 10) {
                throw new \Exception('Unable to generate unique sale number after 10 attempts');
            }
            
            return $newSaleNumber;
        });
    }

    /**
     * Get sales dashboard statistics
     */
    public function dashboard()
    {
        $request = request();
        $company = company();
        
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Get date range from request or default to last 30 days
        $endDate = $request->input('end_date', now()->toDateString());
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());

        // Total Sales Statistics
        $totalSales = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->count();

        $totalRevenue = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->sum('total');

        $totalPaidSales = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->count();

        $totalPendingSales = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->where('status', 'pending')
            ->count();

        $averageSaleValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Sales by Status
        $salesByStatus = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get();

        // Daily Sales Trend (last 30 days or date range)
        $dailySales = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sold_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Top Selling Products
        $topProducts = DB::table('sale_details')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('items', 'sale_details.item_id', '=', 'items.id')
            ->where('sales.company_id', $company->id)
            ->whereBetween('sales.sold_at', [$startDate, $endDate])
            ->select(
                'items.name as product_name',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.total) as total_revenue')
            )
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Monthly comparison (current month vs previous month)
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthEnd = now()->endOfMonth()->toDateString();
        $previousMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $previousMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

        $currentMonthRevenue = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('total');

        $previousMonthRevenue = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total');

        $monthlyGrowth = $previousMonthRevenue > 0 
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
            : 0;

        // Recent sales
        $recentSales = Sale::where('company_id', $company->id)
            ->with(['patient', 'user'])
            ->orderBy('sold_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'xid' => $sale->xid,
                    'sale_number' => $sale->sale_number,
                    'patient_name' => $sale->patient ? $sale->patient->name : 'N/A',
                    'total' => $sale->total,
                    'status' => $sale->status,
                    'sold_at' => $sale->sold_at,
                ];
            });

        // Sales by hour of day (for the date range)
        $salesByHour = Sale::where('company_id', $company->id)
            ->whereBetween('sold_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(sold_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy(DB::raw('HOUR(sold_at)'))
            ->orderBy('hour', 'asc')
            ->get();

        return response()->json([
            'summary' => [
                'total_sales' => $totalSales,
                'total_revenue' => round($totalRevenue, 2),
                'average_sale_value' => round($averageSaleValue, 2),
                'total_paid_sales' => $totalPaidSales,
                'total_pending_sales' => $totalPendingSales,
                'current_month_revenue' => round($currentMonthRevenue, 2),
                'previous_month_revenue' => round($previousMonthRevenue, 2),
                'monthly_growth' => round($monthlyGrowth, 2),
            ],
            'sales_by_status' => $salesByStatus,
            'daily_sales' => $dailySales,
            'top_products' => $topProducts,
            'recent_sales' => $recentSales,
            'sales_by_hour' => $salesByHour,
        ]);
    }
}
