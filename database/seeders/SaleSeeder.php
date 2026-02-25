<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Item;
use App\Models\Patient;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
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

        $patients = Patient::with('user')->get();
        $items = Item::all();
        $adminUser = User::where('email', 'admin@example.com')->first();
        $clinics = ClinicLocation::where('company_id', $company->id)->get();

        if ($patients->isEmpty() || $items->isEmpty() || !$adminUser) {
            $this->command->error('No se encontraron pacientes, productos o usuario admin. Por favor ejecute esos seeders primero.');
            return;
        }

        // Generate sales for the last 60 days with various times throughout the day
        $sales = [];
        $statuses = ['paid', 'paid', 'paid', 'pending']; // 75% paid, 25% pending
        $hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]; // Business hours
        
        // Get patient emails
        $patientEmails = [
            'maria.garcia@email.com',
            'carlos.rodriguez@email.com',
            'ana.martinez@email.com',
            'luis.hernandez@email.com',
            'rosa.lopez@email.com',
            'diego.ramirez@email.com',
        ];

        // Popular item combinations for sales
        $itemCombinations = [
            [
                ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
                ['sku' => 'HIG-PAS-007', 'quantity' => 1, 'price' => 45.00],
            ],
            [
                ['sku' => 'SERV-LIM-016', 'quantity' => 1, 'price' => 800.00],
                ['sku' => 'PROT-GUA-009', 'quantity' => 2, 'price' => 5.00],
                ['sku' => 'HIG-HIL-008', 'quantity' => 1, 'price' => 25.00],
            ],
            [
                ['sku' => 'REST-RES-004', 'quantity' => 1, 'price' => 350.00],
                ['sku' => 'ANES-GEL-012', 'quantity' => 1, 'price' => 75.00],
            ],
            [
                ['sku' => 'SERV-EXT-017', 'quantity' => 1, 'price' => 1200.00],
                ['sku' => 'ANES-LID-011', 'quantity' => 1, 'price' => 150.00],
                ['sku' => 'PROT-GUA-009', 'quantity' => 1, 'price' => 5.00],
            ],
            [
                ['sku' => 'SERV-LIM-016', 'quantity' => 1, 'price' => 800.00],
                ['sku' => 'HIG-PAS-007', 'quantity' => 1, 'price' => 45.00],
            ],
            [
                ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
            ],
            [
                ['sku' => 'REST-RES-004', 'quantity' => 2, 'price' => 350.00],
                ['sku' => 'ANES-GEL-012', 'quantity' => 2, 'price' => 75.00],
                ['sku' => 'PROT-MAS-010', 'quantity' => 2, 'price' => 3.00],
            ],
        ];

        $saleNumber = 1;
        
        // Create sales for last 60 days
        for ($i = 60; $i >= 0; $i--) {
            // Create 1-3 sales per day (more on weekdays, less on weekends)
            $date = now()->subDays($i);
            $isWeekend = $date->isWeekend();
            $salesPerDay = $isWeekend ? rand(0, 1) : rand(2, 4);
            
            for ($j = 0; $j < $salesPerDay; $j++) {
                $hour = $hours[array_rand($hours)];
                $minute = rand(0, 59);
                
                $sales[] = [
                    'sale_number' => 'S-' . $date->format('Ym') . '-' . str_pad($saleNumber++, 4, '0', STR_PAD_LEFT),
                    'sold_at' => $date->format('Y-m-d') . ' ' . sprintf('%02d:%02d:00', $hour, $minute),
                    'status' => $statuses[array_rand($statuses)],
                    'patient_email' => $patientEmails[array_rand($patientEmails)],
                    'items' => $itemCombinations[array_rand($itemCombinations)],
                ];
            }
        }
        
        $this->command->info("Creando " . count($sales) . " ventas...");

        foreach ($sales as $saleData) {
            $patient = $patients->where('user.email', $saleData['patient_email'])->first();

            if (!$patient) {
                $this->command->warn("No se encontró el paciente con email: {$saleData['patient_email']}");
                continue;
            }

            $subtotal = 0.0;
            $validItems = [];

            foreach ($saleData['items'] as $itemData) {
                $item = $items->where('sku', $itemData['sku'])->first();
                if ($item) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['price'];
                    $subtotal += $itemSubtotal;
                    $validItems[] = [
                        'item' => $item,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'subtotal' => $itemSubtotal,
                    ];
                }
            }

            if (empty($validItems)) {
                $this->command->warn("No se encontraron productos válidos para la venta: {$saleData['sale_number']}");
                continue;
            }

            $tax = $subtotal * 0.16; // IVA 16%
            $discount = 0.00;
            $total = $subtotal + $tax - $discount;

            // Crear venta
            $sale = new Sale();
            $sale->company_id = $company->id;
            // Assign random clinic
            $clinic = $clinics->isNotEmpty() ? $clinics->random() : null;
            $sale->clinic_id = $clinic ? $clinic->id : null;

            $sale->patient_id = $patient->id;
            $sale->user_id = $adminUser->id; // vendedor
            $sale->sale_number = $saleData['sale_number'];
            $sale->sold_at = $saleData['sold_at'];
            $sale->status = $saleData['status'];
            $sale->subtotal = $subtotal;
            $sale->tax = $tax;
            $sale->discount = $discount;
            $sale->total = $total;
            $sale->save();

            // Crear detalles de la venta
            foreach ($validItems as $detail) {
                $saleDetail = new SaleDetail();
                $saleDetail->company_id = $company->id;
                $saleDetail->sale_id = $sale->id;
                $saleDetail->item_id = $detail['item']->id;
                $saleDetail->product_name = $detail['item']->name;
                $saleDetail->quantity = $detail['quantity'];
                $saleDetail->price_at_time = $detail['price'];
                $saleDetail->subtotal = $detail['subtotal'];
                $saleDetail->total = $detail['subtotal'];
                $saleDetail->save();
            }

            $this->command->info("Venta creada: {$saleData['sale_number']} - {$patient->user->name}");
        }

        $this->command->info('¡Ventas creadas correctamente!');
    }
}
