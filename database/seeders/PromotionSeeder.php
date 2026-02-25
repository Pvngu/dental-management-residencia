<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemBrand;
use App\Models\Company;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
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

        $items = Item::all();
        $categories = ItemCategory::all();
        $brands = ItemBrand::all();

        if ($items->isEmpty() || $categories->isEmpty() || $brands->isEmpty()) {
            $this->command->error('No se encontraron productos, categorías o marcas. Por favor ejecute esos seeders primero.');
            return;
        }

        $promotions = [
            [
                'name' => 'Descuento de Temporada - Productos de Higiene',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'start_date' => '2024-07-01',
                'end_date' => '2024-07-31',
                'is_active' => true,
                'targets' => [
                    ['type' => 'category', 'name' => 'Productos de Higiene']
                ]
            ],
            [
                'name' => 'Oferta Especial 3M ESPE',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'start_date' => '2024-08-01',
                'end_date' => '2024-08-15',
                'is_active' => true,
                'targets' => [
                    ['type' => 'brand', 'name' => '3M ESPE']
                ]
            ],
            [
                'name' => 'Descuento Fijo - Resina Compuesta',
                'discount_type' => 'fixed',
                'discount_value' => 50.00,
                'start_date' => '2024-06-15',
                'end_date' => '2024-12-15',
                'is_active' => true,
                'targets' => [
                    ['type' => 'product', 'sku' => 'REST-RES-004']
                ]
            ],
            [
                'name' => 'Promoción de Verano - Instrumentos',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'start_date' => '2024-06-21',
                'end_date' => '2024-09-21',
                'is_active' => true,
                'targets' => [
                    ['type' => 'category', 'name' => 'Instrumentos Dentales']
                ]
            ],
            [
                'name' => 'Oferta Especial Kerr',
                'discount_type' => 'percentage',
                'discount_value' => 12.00,
                'start_date' => '2024-07-10',
                'end_date' => '2024-08-10',
                'is_active' => true,
                'targets' => [
                    ['type' => 'brand', 'name' => 'Kerr']
                ]
            ],
            [
                'name' => 'Descuento Anestésicos',
                'discount_type' => 'percentage',
                'discount_value' => 8.00,
                'start_date' => '2024-05-01',
                'end_date' => '2024-11-01',
                'is_active' => true,
                'targets' => [
                    ['type' => 'category', 'name' => 'Anestésicos']
                ]
            ],
            [
                'name' => 'Promoción Equipos de Protección',
                'discount_type' => 'fixed',
                'discount_value' => 25.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'is_active' => true,
                'targets' => [
                    ['type' => 'category', 'name' => 'Equipos de Protección']
                ]
            ],
            [
                'name' => 'Oferta Lidocaína',
                'discount_type' => 'fixed',
                'discount_value' => 15.00,
                'start_date' => '2024-08-01',
                'end_date' => '2024-09-30',
                'is_active' => false, // Promoción inactiva
                'targets' => [
                    ['type' => 'product', 'sku' => 'ANES-LID-011']
                ]
            ]
        ];

        foreach ($promotions as $promotionData) {
            // Crear promoción
            $promotion = new Promotion();
            $promotion->company_id = $company->id;
            $promotion->name = $promotionData['name'];
            $promotion->discount_type = $promotionData['discount_type'];
            $promotion->discount_value = $promotionData['discount_value'];
            $promotion->start_date = $promotionData['start_date'];
            $promotion->end_date = $promotionData['end_date'];
            $promotion->is_active = $promotionData['is_active'];
            $promotion->save();

            // Crear targets de la promoción
            foreach ($promotionData['targets'] as $targetData) {
                $target = new PromotionTarget();
                $target->promotion_id = $promotion->id;
                $target->company_id = $company->id;
                $target->target_type = $targetData['type'];

                switch ($targetData['type']) {
                    case 'product':
                        $item = $items->where('sku', $targetData['sku'])->first();
                        if ($item) {
                            $target->target_id = $item->id;
                        }
                        break;
                    
                    case 'category':
                        $category = $categories->where('name', $targetData['name'])->first();
                        if ($category) {
                            $target->target_id = $category->id;
                        }
                        break;
                    
                    case 'brand':
                        $brand = $brands->where('name', $targetData['name'])->first();
                        if ($brand) {
                            $target->target_id = $brand->id;
                        }
                        break;
                }

                if (isset($target->target_id)) {
                    $target->save();
                } else {
                    $this->command->warn("No se pudo encontrar el target para la promoción: {$promotionData['name']}");
                }
            }

            $this->command->info("Promoción creada: {$promotionData['name']}");
        }

        $this->command->info('¡Promociones creadas correctamente!');
    }
}