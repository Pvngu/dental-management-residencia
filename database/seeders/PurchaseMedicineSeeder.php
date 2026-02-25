<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Medicine;
use App\Models\OrderMedicine;
use App\Models\PurchaseMedicine;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseMedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('No se encontró ninguna compañía. Por favor ejecute el seeder de compañías primero.');
            return;
        }

        $medicines = Medicine::with('item')->where('company_id', $company->id)->get();
        $clinics = ClinicLocation::where('company_id', $company->id)->get();

        if ($medicines->isEmpty()) {
            $this->command->error('No se encontraron medicinas. Por favor ejecute el MedicineSeeder primero.');
            return;
        }

        $paymentTypes = ['cash', 'bank_transfer', 'check', 'card'];
        $paymentStatuses = ['paid', 'unpaid', 'partial'];

        // Create 20 purchase orders
        for ($i = 0; $i < 20; $i++) {
            DB::transaction(function () use ($company, $medicines, $paymentTypes, $paymentStatuses, $i, $clinics) {
                $deliveryDate = Carbon::now()->subDays(rand(1, 90));
                
                $purchase = new PurchaseMedicine();
                $purchase->reference_no = 'PUR-' . strtoupper(uniqid());
                $purchase->delivery_date = $deliveryDate;
                $purchase->payment_type = $paymentTypes[array_rand($paymentTypes)];
                $purchase->payment_status = $paymentStatuses[array_rand($paymentStatuses)];
                $purchase->note = 'Orden de compra de medicamentos #' . ($i + 1);
                $purchase->company_id = $company->id;
                
                // Assign random clinic
                $clinic = $clinics->isNotEmpty() ? $clinics->random() : null;
                $purchase->clinic_id = $clinic ? $clinic->id : null;
                
                // Initialize totals
                $purchase->subtotal = 0;
                $purchase->tax = 0;
                $purchase->discount = 0;
                $purchase->adjustments = 0;
                $purchase->total = 0;
                $purchase->save();

                $subtotal = 0;
                $tax = 0;

                // Add 1-5 medicines to this purchase
                $numItems = rand(1, 5);
                $selectedMedicines = $medicines->random($numItems);

                foreach ($selectedMedicines as $medicine) {
                    $quantity = rand(10, 100);
                    // Generate a cost price slightly lower than sales price (assuming item price is sales price)
                    // If purchase_price exists on Item, use it, otherwise estimate it.
                    // Medicine linked to Item, Item usually has price. 
                    // Let's assume a margin.
                    $salesPrice = $medicine->item->price ?? 10;
                    $rate = $salesPrice * 0.7; // 30% margin
                    $amount = $quantity * $rate;
                    
                    $orderMedicine = new OrderMedicine();
                    $orderMedicine->purchase_medicine_id = $purchase->id;
                    $orderMedicine->medicine_id = $medicine->id;
                    $orderMedicine->quantity = $quantity;
                    $orderMedicine->rate = round($rate, 2);
                    $orderMedicine->amount = round($amount, 2);
                    $orderMedicine->company_id = $company->id;
                    
                    // Expiry and Lot info
                    $orderMedicine->lot_no = 'LOT-' . rand(1000, 9999);
                    $orderMedicine->expiry_date = Carbon::now()->addMonths(rand(6, 24));
                    
                    $orderMedicine->save();

                    $subtotal += $amount;
                    
                    // Update medicine available quantity (handled by observer usually, but let's be safe or just trigger update)
                    // In many systems, purchasing increases stock.
                    // If there is an Item observer or similar logic, it might handle it.
                    // For now, I will just update the Item quantity directly if appropriate, 
                    // but looking at MedicineSeeder, quantity is on Item? 
                    // MedicineSeeder set 'available_quantity' on Item? No, let's check.
                    // MedicineSeeder created Item with available_quantity.
                    // So we should update Item available_quantity.
                    
                    $medicine->item->available_quantity += $quantity;
                    $medicine->item->save();
                }

                // Random tax (e.g. 10% or 0%)
                $taxPercentage = rand(0, 1) ? 0.10 : 0;
                $tax = $subtotal * $taxPercentage;
                
                // Random discount
                $discount = rand(0, 1) ? rand(10, 50) : 0;

                $purchase->subtotal = $subtotal;
                $purchase->tax = $tax;
                $purchase->discount = $discount;
                $purchase->total = $subtotal + $tax - $discount + $purchase->adjustments;
                $purchase->save();
            });
        }

        $this->command->info('Seeder de compras de medicinas completado exitosamente.');
    }
}
