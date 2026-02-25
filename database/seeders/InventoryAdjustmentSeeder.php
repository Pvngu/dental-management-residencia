<?php

namespace Database\Seeders;

use App\Models\InventoryAdjustment;
use App\Models\AdjustmentItem;
use App\Models\AdjustmentsReason;
use App\Models\Item;
use App\Models\Company;
use App\Models\ClinicLocation;
use Illuminate\Database\Seeder;

class InventoryAdjustmentSeeder extends Seeder
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

        $reasons = AdjustmentsReason::all();
        $items = Item::where('type', 'goods')->get();
        $clinics = ClinicLocation::where('company_id', $company->id)->get();

        if ($reasons->isEmpty()) {
            $this->command->error('No se encontraron razones de ajuste. Por favor ejecute el seeder de razones primero.');
            return;
        }

        if ($items->isEmpty()) {
            $this->command->error('No se encontraron productos. Por favor ejecute el seeder de productos primero.');
            return;
        }

        $adjustments = [
            [
                'reference_number' => 'ADJ-2024-001',
                'date' => '2024-01-15',
                'reason' => 'Error en Conteo',
                'description' => 'Ajuste de inventario por diferencias encontradas en auditoría mensual',
                'items' => [
                    ['item_sku' => 'INST-ESP-001', 'quantity_before' => 50, 'quantity_after' => 48],
                    ['item_sku' => 'PROT-GUA-009', 'quantity_before' => 1000, 'quantity_after' => 995],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-002',
                'date' => '2024-02-20',
                'reason' => 'Producto Vencido',
                'description' => 'Retiro de productos vencidos del inventario',
                'items' => [
                    ['item_sku' => 'ANES-LID-011', 'quantity_before' => 100, 'quantity_after' => 95],
                    ['item_sku' => 'ANES-GEL-012', 'quantity_before' => 50, 'quantity_after' => 48],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-003',
                'date' => '2024-03-10',
                'reason' => 'Producto Dañado',
                'description' => 'Ajuste por productos dañados durante el transporte',
                'items' => [
                    ['item_sku' => 'REST-RES-004', 'quantity_before' => 100, 'quantity_after' => 97],
                    ['item_sku' => 'IMP-ALG-013', 'quantity_before' => 20, 'quantity_after' => 19],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-004',
                'date' => '2024-04-05',
                'reason' => 'Muestra Médica',
                'description' => 'Productos utilizados como muestras para demostraciones',
                'items' => [
                    ['item_sku' => 'HIG-PAS-007', 'quantity_before' => 150, 'quantity_after' => 145],
                    ['item_sku' => 'HIG-HIL-008', 'quantity_before' => 500, 'quantity_after' => 495],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-005',
                'date' => '2024-05-12',
                'reason' => 'Capacitación',
                'description' => 'Material utilizado en capacitación del nuevo personal',
                'items' => [
                    ['item_sku' => 'INST-SON-002', 'quantity_before' => 25, 'quantity_after' => 23],
                    ['item_sku' => 'INST-EXC-003', 'quantity_before' => 30, 'quantity_after' => 28],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-006',
                'date' => '2024-06-18',
                'reason' => 'Ajuste de Entrada',
                'description' => 'Incremento por entrada no registrada correctamente',
                'items' => [
                    ['item_sku' => 'PROT-MAS-010', 'quantity_before' => 500, 'quantity_after' => 520],
                    ['item_sku' => 'REST-AMA-005', 'quantity_before' => 200, 'quantity_after' => 205],
                ]
            ],
            [
                'reference_number' => 'ADJ-2024-007',
                'date' => '2024-07-08',
                'reason' => 'Devolución a Proveedor',
                'description' => 'Devolución de lote defectuoso al fabricante',
                'items' => [
                    ['item_sku' => 'REST-ION-006', 'quantity_before' => 75, 'quantity_after' => 70],
                ]
            ]
        ];

        foreach ($adjustments as $adjustmentData) {
            $reason = $reasons->where('name', $adjustmentData['reason'])->first();
            
            if (!$reason) {
                $this->command->warn("No se encontró la razón: {$adjustmentData['reason']}");
                continue;
            }

            // Crear ajuste de inventario
            $adjustment = new InventoryAdjustment();
            $adjustment->company_id = $company->id;
            // Assign random clinic
            $clinic = $clinics->isNotEmpty() ? $clinics->random() : null;
            $adjustment->clinic_id = $clinic ? $clinic->id : null;

            $adjustment->reference_number = $adjustmentData['reference_number'];
            $adjustment->date = $adjustmentData['date'];
            $adjustment->adjustments_reason_id = $reason->id;
            $adjustment->description = $adjustmentData['description'];
            $adjustment->save();

            // Crear items del ajuste
            foreach ($adjustmentData['items'] as $itemData) {
                $item = $items->where('sku', $itemData['item_sku'])->first();
                
                if (!$item) {
                    $this->command->warn("No se encontró el producto con SKU: {$itemData['item_sku']}");
                    continue;
                }

                $adjustmentItem = new AdjustmentItem();
                $adjustmentItem->adjustment_id = $adjustment->id;
                $adjustmentItem->item_id = $item->id;
                $adjustmentItem->quantity_before = $itemData['quantity_before'];
                $adjustmentItem->quantity_after = $itemData['quantity_after'];
                $adjustmentItem->company_id = $company->id;
                $adjustmentItem->save();

                // Actualizar cantidad disponible del item
                $item->available_quantity = $itemData['quantity_after'];
                $item->save();
            }

            $this->command->info("Ajuste de inventario creado: {$adjustmentData['reference_number']}");
        }

        $this->command->info('¡Ajustes de inventario creados correctamente!');
    }
}