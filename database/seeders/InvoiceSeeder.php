<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Patient;
use App\Models\User;
use App\Models\Item;
use App\Models\Company;
use App\Models\ClinicLocation;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
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

        $invoices = [
            [
                'invoice_number' => 'INV-2024-001',
                'date_of_issue' => '2024-01-15',
                'payment_due_on' => '2024-01-30',
                'status' => 'paid',
                'patient_email' => 'maria.garcia@email.com',
                'items' => [
                    ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
                    ['sku' => 'SERV-LIM-016', 'quantity' => 1, 'price' => 800.00],
                    ['sku' => 'HIG-PAS-007', 'quantity' => 1, 'price' => 45.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-002',
                'date_of_issue' => '2024-01-20',
                'payment_due_on' => '2024-02-05',
                'status' => 'pending',
                'patient_email' => 'carlos.rodriguez@email.com',
                'items' => [
                    ['sku' => 'SERV-EXT-017', 'quantity' => 1, 'price' => 1200.00],
                    ['sku' => 'ANES-LID-011', 'quantity' => 2, 'price' => 150.00],
                    ['sku' => 'PROT-GUA-009', 'quantity' => 2, 'price' => 5.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-003',
                'date_of_issue' => '2024-02-10',
                'payment_due_on' => '2024-02-25',
                'status' => 'paid',
                'patient_email' => 'ana.martinez@email.com',
                'items' => [
                    ['sku' => 'REST-RES-004', 'quantity' => 1, 'price' => 350.00],
                    ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
                    ['sku' => 'ANES-GEL-012', 'quantity' => 1, 'price' => 75.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-004',
                'date_of_issue' => '2024-02-28',
                'payment_due_on' => '2024-03-15',
                'status' => 'overdue',
                'patient_email' => 'luis.hernandez@email.com',
                'items' => [
                    ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
                    ['sku' => 'HIG-HIL-008', 'quantity' => 1, 'price' => 25.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-005',
                'date_of_issue' => '2024-03-15',
                'payment_due_on' => '2024-03-30',
                'status' => 'paid',
                'patient_email' => 'rosa.lopez@email.com',
                'items' => [
                    ['sku' => 'SERV-LIM-016', 'quantity' => 1, 'price' => 800.00],
                    ['sku' => 'HIG-PAS-007', 'quantity' => 1, 'price' => 45.00],
                    ['sku' => 'PROT-MAS-010', 'quantity' => 1, 'price' => 3.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-006',
                'date_of_issue' => '2024-04-05',
                'payment_due_on' => '2024-04-20',
                'status' => 'pending',
                'patient_email' => 'diego.ramirez@email.com',
                'items' => [
                    ['sku' => 'REST-AMA-005', 'quantity' => 1, 'price' => 600.00],
                    ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00],
                    ['sku' => 'ANES-LID-011', 'quantity' => 1, 'price' => 150.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-007',
                'date_of_issue' => '2024-04-22',
                'payment_due_on' => '2024-05-07',
                'status' => 'paid',
                'patient_email' => 'patricia.morales@email.com',
                'items' => [
                    ['sku' => 'REST-ION-006', 'quantity' => 2, 'price' => 400.00],
                    ['sku' => 'SERV-CON-015', 'quantity' => 1, 'price' => 500.00]
                ]
            ],
            [
                'invoice_number' => 'INV-2024-008',
                'date_of_issue' => '2024-05-10',
                'payment_due_on' => '2024-05-25',
                'status' => 'pending',
                'patient_email' => 'alejandro.jimenez@email.com',
                'items' => [
                    ['sku' => 'SERV-EXT-017', 'quantity' => 2, 'price' => 1200.00],
                    ['sku' => 'ANES-LID-011', 'quantity' => 3, 'price' => 150.00],
                    ['sku' => 'PROT-GUA-009', 'quantity' => 4, 'price' => 5.00]
                ]
            ]
        ];

        foreach ($invoices as $invoiceData) {
            // Buscar paciente por email
            $patient = $patients->where('user.email', $invoiceData['patient_email'])->first();
            
            if (!$patient) {
                $this->command->warn("No se encontró el paciente con email: {$invoiceData['patient_email']}");
                continue;
            }

            // Calcular totales
            $subtotal = 0;
            $validItems = [];
            
            foreach ($invoiceData['items'] as $itemData) {
                $item = $items->where('sku', $itemData['sku'])->first();
                if ($item) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['price'];
                    $subtotal += $itemSubtotal;
                    $validItems[] = [
                        'item' => $item,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'subtotal' => $itemSubtotal
                    ];
                }
            }

            if (empty($validItems)) {
                $this->command->warn("No se encontraron productos válidos para la factura: {$invoiceData['invoice_number']}");
                continue;
            }

            $tax = $subtotal * 0.16; // IVA 16%
            $total = $subtotal + $tax;

            // Crear factura
            $invoice = new Invoice();
            $invoice->company_id = $company->id;
            // Assign random clinic
            $clinic = $clinics->isNotEmpty() ? $clinics->random() : null;
            $invoice->clinic_id = $clinic ? $clinic->id : null;

            $invoice->patient_id = $patient->id;
            $invoice->created_by = $adminUser->id;
            $invoice->invoice_number = $invoiceData['invoice_number'];
            $invoice->date_of_issue = $invoiceData['date_of_issue'];
            $invoice->payment_due_on = $invoiceData['payment_due_on'];
            $invoice->status = $invoiceData['status'];
            $invoice->company_name = $company->name;
            $invoice->person_name = $patient->user->name . ' ' . $patient->user->last_name;
            $invoice->email = $patient->user->email;
            $invoice->phone_number = $patient->user->phone;
            $invoice->subtotal = $subtotal;
            $invoice->tax = $tax;
            $invoice->discount = 0.00;
            $invoice->total_payable = $total;
            $invoice->save();

            // Crear detalles de la factura
            foreach ($validItems as $itemDetail) {
                $invoiceDetail = new InvoiceDetail();
                $invoiceDetail->company_id = $company->id;
                $invoiceDetail->invoice_id = $invoice->id;
                $invoiceDetail->item_id = $itemDetail['item']->id;
                $invoiceDetail->product_name = $itemDetail['item']->name;
                $invoiceDetail->quantity = $itemDetail['quantity'];
                $invoiceDetail->price_at_time = $itemDetail['price'];
                $invoiceDetail->subtotal = $itemDetail['subtotal'];
                $invoiceDetail->total = $itemDetail['subtotal'];
                $invoiceDetail->save();
            }

            $this->command->info("Factura creada: {$invoiceData['invoice_number']} - {$patient->user->name}");
        }

        $this->command->info('¡Facturas creadas correctamente!');
    }
}