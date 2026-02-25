<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemBrand;
use App\Models\ItemManufacture;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
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

        $categories = ItemCategory::all();
        $brands = ItemBrand::all();
        $manufacturers = ItemManufacture::all();

        if ($categories->isEmpty() || $brands->isEmpty() || $manufacturers->isEmpty()) {
            $this->command->error('No se encontraron categorías, marcas o fabricantes. Por favor ejecute esos seeders primero.');
            return;
        }

        // Create suppliers if they don't exist
        $this->createSuppliersIfNotExist($company);
        $suppliers = Supplier::where('company_id', $company->id)->get();

        $items = [
            // Instrumentos Dentales
            [
                'name' => 'Espejo Dental #5',
                'description' => 'Espejo dental con mango de acero inoxidable, tamaño estándar',
                'category' => 'Instrumentos Dentales',
                'brand' => 'Dentsply Sirona',
                'manufacturer' => 'Dentsply Sirona Inc.',
                'unit' => 'cm',
                'available_quantity' => 50,
                'sku' => 'INST-ESP-001',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 150.00,
                'sale_description' => 'Espejo dental de alta calidad para exámenes bucales',
                'is_purchasable' => true,
                'cost_price' => 85.00,
                'purchase_description' => 'Compra directa del fabricante',
                'supplier' => 'Dentsply Sirona Mexico'
            ],
            [
                'name' => 'Sonda Periodontal',
                'description' => 'Sonda para medición de bolsas periodontales con marcas de 1-15mm',
                'category' => 'Instrumentos Dentales',
                'brand' => 'Kerr',
                'manufacturer' => 'Kerr Corporation',
                'unit' => 'cm',
                'available_quantity' => 25,
                'sku' => 'INST-SON-002',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 180.00,
                'sale_description' => 'Sonda periodontal de precisión con marcas calibradas',
                'is_purchasable' => true,
                'cost_price' => 120.00,
                'purchase_description' => 'Distribuidor autorizado Kerr',
                'supplier' => 'Kerr Mexico'
            ],
            [
                'name' => 'Excavador Dental',
                'description' => 'Excavador para remoción de caries, doble punta',
                'category' => 'Instrumentos Dentales',
                'brand' => 'Dentsply Sirona',
                'manufacturer' => 'Dentsply Sirona Inc.',
                'unit' => 'cm',
                'available_quantity' => 30,
                'sku' => 'INST-EXC-003',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 165.00,
                'sale_description' => 'Excavador dental de doble punta para procedimientos restaurativos',
                'is_purchasable' => true,
                'cost_price' => 95.00,
                'purchase_description' => 'Compra directa del fabricante',
                'supplier' => 'Dentsply Sirona Mexico'
            ],

            // Materiales de Restauración
            [
                'name' => 'Resina Compuesta A2',
                'description' => 'Resina compuesta fotopolimerizable color A2',
                'category' => 'Materiales de Restauración',
                'brand' => '3M ESPE',
                'manufacturer' => '3M Company',
                'unit' => 'g',
                'available_quantity' => 100,
                'sku' => 'REST-RES-004',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 320.00,
                'sale_description' => 'Resina compuesta de alta estética para restauraciones anteriores',
                'is_purchasable' => true,
                'cost_price' => 220.00,
                'purchase_description' => 'Distribuidor oficial 3M',
                'supplier' => '3M Mexico'
            ],
            [
                'name' => 'Amalgama Dental',
                'description' => 'Amalgama dental sin mercurio para restauraciones posteriores',
                'category' => 'Materiales de Restauración',
                'brand' => 'Kerr',
                'manufacturer' => 'Kerr Corporation',
                'unit' => 'g',
                'available_quantity' => 200,
                'sku' => 'REST-AMA-005',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 280.00,
                'sale_description' => 'Amalgama sin mercurio para restauraciones durables',
                'is_purchasable' => true,
                'cost_price' => 195.00,
                'purchase_description' => 'Distribuidor autorizado Kerr',
                'supplier' => 'Kerr Mexico'
            ],
            [
                'name' => 'Ionómero de Vidrio',
                'description' => 'Cemento de ionómero de vidrio para restauraciones',
                'category' => 'Materiales de Restauración',
                'brand' => 'GC Corporation',
                'manufacturer' => 'GC Corporation',
                'unit' => 'g',
                'available_quantity' => 75,
                'sku' => 'REST-ION-006',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 250.00,
                'sale_description' => 'Ionómero de vidrio con liberación de flúor',
                'is_purchasable' => true,
                'cost_price' => 170.00,
                'purchase_description' => 'Distribuidor GC Mexico',
                'supplier' => 'GC Corporation Mexico'
            ],

            // Productos de Higiene
            [
                'name' => 'Pasta Profiláctica',
                'description' => 'Pasta para profilaxis dental con flúor',
                'category' => 'Productos de Higiene',
                'brand' => 'Oral-B',
                'manufacturer' => 'Procter & Gamble',
                'unit' => 'g',
                'available_quantity' => 150,
                'sku' => 'HIG-PAS-007',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 45.00,
                'sale_description' => 'Pasta profiláctica con flúor para limpieza profesional',
                'is_purchasable' => true,
                'cost_price' => 28.00,
                'purchase_description' => 'Distribuidor P&G Mexico',
                'supplier' => 'Procter & Gamble Mexico'
            ],
            [
                'name' => 'Hilo Dental Profesional',
                'description' => 'Hilo dental encerado para uso profesional',
                'category' => 'Productos de Higiene',
                'brand' => 'Oral-B',
                'manufacturer' => 'Procter & Gamble',
                'unit' => 'm',
                'available_quantity' => 500,
                'sku' => 'HIG-HIL-008',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 25.00,
                'sale_description' => 'Hilo dental profesional encerado de alta resistencia',
                'is_purchasable' => true,
                'cost_price' => 15.00,
                'purchase_description' => 'Distribuidor P&G Mexico',
                'supplier' => 'Procter & Gamble Mexico'
            ],

            // Equipos de Protección
            [
                'name' => 'Guantes de Nitrilo',
                'description' => 'Guantes de nitrilo sin polvo, talla M',
                'category' => 'Equipos de Protección',
                'brand' => '3M ESPE',
                'manufacturer' => '3M Company',
                'unit' => 'g',
                'available_quantity' => 1000,
                'sku' => 'PROT-GUA-009',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 2.50,
                'sale_description' => 'Guantes de nitrilo sin polvo para protección',
                'is_purchasable' => true,
                'cost_price' => 1.80,
                'purchase_description' => 'Distribuidor oficial 3M',
                'supplier' => '3M Mexico'
            ],
            [
                'name' => 'Mascarillas Quirúrgicas',
                'description' => 'Mascarillas quirúrgicas desechables, 3 capas',
                'category' => 'Equipos de Protección',
                'brand' => '3M ESPE',
                'manufacturer' => '3M Company',
                'unit' => 'g',
                'available_quantity' => 500,
                'sku' => 'PROT-MAS-010',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 3.50,
                'sale_description' => 'Mascarillas quirúrgicas de 3 capas con alta filtración',
                'is_purchasable' => true,
                'cost_price' => 2.20,
                'purchase_description' => 'Distribuidor oficial 3M',
                'supplier' => '3M Mexico'
            ],

            // Anestésicos
            [
                'name' => 'Lidocaína 2% con Epinefrina',
                'description' => 'Anestésico local en cartuchos de 1.8ml',
                'category' => 'Anestésicos',
                'brand' => 'Septodont',
                'manufacturer' => 'Septodont',
                'unit' => 'g',
                'available_quantity' => 100,
                'sku' => 'ANES-LID-011',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 12.50,
                'sale_description' => 'Anestésico local con epinefrina para procedimientos dentales',
                'is_purchasable' => true,
                'cost_price' => 8.75,
                'purchase_description' => 'Distribuidor autorizado Septodont',
                'supplier' => 'Septodont Mexico'
            ],
            [
                'name' => 'Gel Anestésico Tópico',
                'description' => 'Gel anestésico tópico sabor cereza, 20%',
                'category' => 'Anestésicos',
                'brand' => 'Ultradent',
                'manufacturer' => 'Ultradent Products Inc.',
                'unit' => 'g',
                'available_quantity' => 50,
                'sku' => 'ANES-GEL-012',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 85.00,
                'sale_description' => 'Gel anestésico tópico de acción rápida',
                'is_purchasable' => true,
                'cost_price' => 60.00,
                'purchase_description' => 'Distribuidor Ultradent Mexico',
                'supplier' => 'Ultradent Mexico'
            ],

            // Material de Impresión
            [
                'name' => 'Alginato para Impresiones',
                'description' => 'Alginato de fraguado rápido para impresiones dentales',
                'category' => 'Material de Impresión',
                'brand' => 'Dentsply Sirona',
                'manufacturer' => 'Dentsply Sirona Inc.',
                'unit' => 'kg',
                'available_quantity' => 20,
                'sku' => 'IMP-ALG-013',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 120.00,
                'sale_description' => 'Alginato de fraguado rápido y fácil mezclado',
                'is_purchasable' => true,
                'cost_price' => 85.00,
                'purchase_description' => 'Compra directa del fabricante',
                'supplier' => 'Dentsply Sirona Mexico'
            ],
            [
                'name' => 'Silicona de Adición',
                'description' => 'Silicona de adición para impresiones de precisión',
                'category' => 'Material de Impresión',
                'brand' => 'Ivoclar Vivadent',
                'manufacturer' => 'Ivoclar Vivadent AG',
                'unit' => 'g',
                'available_quantity' => 80,
                'sku' => 'IMP-SIL-014',
                'type' => 'goods',
                'is_sellable' => true,
                'sale_price' => 450.00,
                'sale_description' => 'Silicona de adición de alta precisión para impresiones',
                'is_purchasable' => true,
                'cost_price' => 320.00,
                'purchase_description' => 'Distribuidor Ivoclar Vivadent Mexico',
                'supplier' => 'Ivoclar Vivadent Mexico'
            ],

            // Servicios
            [
                'name' => 'Consulta General',
                'description' => 'Consulta dental general con examen completo',
                'category' => 'Instrumentos Dentales',
                'brand' => 'Dentsply Sirona',
                'manufacturer' => 'Dentsply Sirona Inc.',
                'unit' => null,
                'available_quantity' => 0,
                'sku' => 'SERV-CON-015',
                'type' => 'service',
                'is_sellable' => true,
                'sale_price' => 350.00,
                'sale_description' => 'Consulta dental general con revisión completa',
                'is_purchasable' => false,
                'cost_price' => null,
                'purchase_description' => null,
                'supplier' => null
            ],
            [
                'name' => 'Limpieza Dental',
                'description' => 'Profilaxis dental profesional',
                'category' => 'Productos de Higiene',
                'brand' => 'Oral-B',
                'manufacturer' => 'Procter & Gamble',
                'unit' => null,
                'available_quantity' => 0,
                'sku' => 'SERV-LIM-016',
                'type' => 'service',
                'is_sellable' => true,
                'sale_price' => 450.00,
                'sale_description' => 'Profilaxis dental profesional con ultrasonido',
                'is_purchasable' => false,
                'cost_price' => null,
                'purchase_description' => null,
                'supplier' => null
            ],
            [
                'name' => 'Extracción Simple',
                'description' => 'Extracción dental simple sin complicaciones',
                'category' => 'Instrumentos Dentales',
                'brand' => 'Dentsply Sirona',
                'manufacturer' => 'Dentsply Sirona Inc.',
                'unit' => null,
                'available_quantity' => 0,
                'sku' => 'SERV-EXT-017',
                'type' => 'service',
                'is_sellable' => true,
                'sale_price' => 550.00,
                'sale_description' => 'Extracción dental simple con anestesia local',
                'is_purchasable' => false,
                'cost_price' => null,
                'purchase_description' => null,
                'supplier' => null
            ]
        ];

        foreach ($items as $itemData) {
            $category = $categories->where('name', $itemData['category'])->first();
            $brand = $brands->where('name', $itemData['brand'])->first();
            $manufacturer = $manufacturers->where('name', $itemData['manufacturer'])->first();

            if (!$category || !$brand || !$manufacturer) {
                $this->command->warn("No se pudo encontrar categoría, marca o fabricante para: {$itemData['name']}");
                continue;
            }

            // Find supplier if specified
            $supplier = null;
            if ($itemData['supplier']) {
                $supplier = $suppliers->where('name', $itemData['supplier'])->first();
            }

            $item = new Item();
            $item->company_id = $company->id;
            $item->name = $itemData['name'];
            $item->description = $itemData['description'];
            $item->category_id = $category->id;
            $item->brand_id = $brand->id;
            $item->manufacturer_id = $manufacturer->id;
            $item->unit = $itemData['unit'];
            $item->available_quantity = $itemData['available_quantity'];
            $item->sku = $itemData['sku'];
            $item->type = $itemData['type'];
            
            // Sales and purchase information
            $item->is_sellable = $itemData['is_sellable'] ?? false;
            $item->sale_price = $itemData['sale_price'];
            $item->sale_description = $itemData['sale_description'];
            $item->is_purchasable = $itemData['is_purchasable'] ?? false;
            $item->cost_price = $itemData['cost_price'];
            $item->purchase_description = $itemData['purchase_description'];
            $item->supplier_id = $supplier ? $supplier->id : null;
            
            // Agregar algunas dimensiones y pesos aleatorios para productos físicos
            if ($itemData['type'] === 'goods') {
                $item->item_length = rand(5, 50);
                $item->item_width = rand(3, 30);
                $item->item_height = rand(1, 20);
                $item->dimension_unit = 'cm';
                $item->weight = rand(10, 500);
                $item->weight_unit = 'g';
            }
            
            $item->save();

            $this->command->info("Producto creado: {$itemData['name']}");
        }

        $this->command->info('¡Productos creados correctamente!');
    }

    private function createSuppliersIfNotExist($company)
    {
        $suppliersData = [
            [
                'name' => 'Dentsply Sirona Mexico',
                'email' => 'ventas@dentsplysirona.mx',
                'phone' => '+52 55 1234 5678',
                'address' => 'Av. Insurgentes Sur 1234, CDMX',
                'contact_person' => 'Carlos Martinez',
                'notes' => 'Distribuidor oficial de Dentsply Sirona',
                'status' => true
            ],
            [
                'name' => 'Kerr Mexico',
                'email' => 'info@kerr.mx',
                'phone' => '+52 55 2345 6789',
                'address' => 'Blvd. Manuel Avila Camacho 567, Naucalpan',
                'contact_person' => 'Ana Rodriguez',
                'notes' => 'Productos Kerr para odontología',
                'status' => true
            ],
            [
                'name' => '3M Mexico',
                'email' => 'dental@3m.mx',
                'phone' => '+52 55 3456 7890',
                'address' => 'Santa Fe Corporate Center, CDMX',
                'contact_person' => 'Miguel Lopez',
                'notes' => 'Productos 3M ESPE y equipos de protección',
                'status' => true
            ],
            [
                'name' => 'GC Corporation Mexico',
                'email' => 'ventas@gc.mx',
                'phone' => '+52 55 4567 8901',
                'address' => 'Zona Rosa, CDMX',
                'contact_person' => 'Patricia Sanchez',
                'notes' => 'Materiales de restauración GC',
                'status' => true
            ],
            [
                'name' => 'Procter & Gamble Mexico',
                'email' => 'b2b@pg.mx',
                'phone' => '+52 55 5678 9012',
                'address' => 'Polanco, CDMX',
                'contact_person' => 'Roberto Fernandez',
                'notes' => 'Productos de higiene oral Oral-B',
                'status' => true
            ],
            [
                'name' => 'Septodont Mexico',
                'email' => 'mexico@septodont.com',
                'phone' => '+52 55 6789 0123',
                'address' => 'Col. Del Valle, CDMX',
                'contact_person' => 'Laura Gutierrez',
                'notes' => 'Anestésicos dentales Septodont',
                'status' => true
            ],
            [
                'name' => 'Ultradent Mexico',
                'email' => 'info@ultradent.mx',
                'phone' => '+52 55 7890 1234',
                'address' => 'Satelite, Edo. Mexico',
                'contact_person' => 'Fernando Morales',
                'notes' => 'Productos Ultradent para endodoncia y estética',
                'status' => true
            ],
            [
                'name' => 'Ivoclar Vivadent Mexico',
                'email' => 'mexico@ivoclarvivadent.com',
                'phone' => '+52 55 8901 2345',
                'address' => 'Lomas de Chapultepec, CDMX',
                'contact_person' => 'Sandra Ramirez',
                'notes' => 'Materiales de impresión y cerámica dental',
                'status' => true
            ]
        ];

        foreach ($suppliersData as $supplierData) {
            $existingSupplier = Supplier::where('company_id', $company->id)
                ->where('name', $supplierData['name'])
                ->first();

            if (!$existingSupplier) {
                $supplier = new Supplier();
                $supplier->company_id = $company->id;
                $supplier->name = $supplierData['name'];
                $supplier->email = $supplierData['email'];
                $supplier->phone = $supplierData['phone'];
                $supplier->address = $supplierData['address'];
                $supplier->contact_person = $supplierData['contact_person'];
                $supplier->notes = $supplierData['notes'];
                $supplier->status = $supplierData['status'];
                $supplier->save();

                $this->command->info("Proveedor creado: {$supplierData['name']}");
            }
        }
    }
}