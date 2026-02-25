<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemBrand;
use App\Models\ItemManufacture;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
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

        // Create or find medicine-specific categories
        $medicineCategory = ItemCategory::firstOrCreate(
            [
                'name' => 'Medicamentos',
                'type' => 'medicine',
                'company_id' => $company->id,
            ],
            [
                'description' => 'Categoría para medicamentos y productos farmacéuticos',
                'is_active' => true,
            ]
        );

        // Create medicine-specific brands
        $pharmaceuticalBrands = [
            ['name' => 'Genéricos', 'description' => 'Medicamentos genéricos'],
            ['name' => 'Pfizer', 'description' => 'Productos farmacéuticos Pfizer'],
            ['name' => 'Bayer', 'description' => 'Productos farmacéuticos Bayer'],
            ['name' => 'Novartis', 'description' => 'Productos farmacéuticos Novartis'],
            ['name' => 'Sanofi', 'description' => 'Productos farmacéuticos Sanofi'],
        ];

        $medicineBrands = [];
        foreach ($pharmaceuticalBrands as $brandData) {
            $brand = ItemBrand::firstOrCreate(
                [
                    'name' => $brandData['name'],
                    'type' => 'medicine',
                    'company_id' => $company->id,
                ],
                [
                    'description' => $brandData['description'],
                    'is_active' => true,
                ]
            );
            $medicineBrands[] = $brand;
        }

        // Get or create a default manufacturer for medicines
        $manufacturers = ItemManufacture::where('company_id', $company->id)->get();
        if ($manufacturers->isEmpty()) {
            $defaultManufacturer = ItemManufacture::create([
                'name' => 'Laboratorio Nacional',
                'description' => 'Fabricante de medicamentos nacional',
                'company_id' => $company->id,
            ]);
        } else {
            $defaultManufacturer = $manufacturers->first();
        }

        $defaultBrand = $medicineBrands[0]; // Use Genéricos as default

        $medicines = [
            [
                'name' => 'Amoxicilina 500mg',
                'description' => 'Antibiótico de amplio espectro para infecciones dentales',
                'salt_composition' => 'Amoxicillin Trihydrate',
                'side_effects' => 'Náuseas, vómito, diarrea, erupciones cutáneas, reacciones alérgicas',
                'sku' => 'MED-AMX-500',
                'available_quantity' => 100,
            ],
            [
                'name' => 'Ibuprofeno 400mg',
                'description' => 'Antiinflamatorio no esteroideo para dolor e inflamación',
                'salt_composition' => 'Ibuprofen',
                'side_effects' => 'Dolor estomacal, náuseas, acidez, mareos, dolor de cabeza',
                'sku' => 'MED-IBU-400',
                'available_quantity' => 150,
            ],
            [
                'name' => 'Paracetamol 500mg',
                'description' => 'Analgésico y antipirético para dolor leve a moderado',
                'salt_composition' => 'Acetaminophen',
                'side_effects' => 'Raras: erupciones cutáneas, alteraciones hepáticas en dosis altas',
                'sku' => 'MED-PAR-500',
                'available_quantity' => 200,
            ],
            [
                'name' => 'Clindamicina 300mg',
                'description' => 'Antibiótico para infecciones odontogénicas severas',
                'salt_composition' => 'Clindamycin Hydrochloride',
                'side_effects' => 'Diarrea, colitis pseudomembranosa, náuseas, sabor metálico',
                'sku' => 'MED-CLI-300',
                'available_quantity' => 75,
            ],
            [
                'name' => 'Metamizol Sódico 500mg',
                'description' => 'Analgésico y antipirético de potencia moderada',
                'salt_composition' => 'Metamizole Sodium',
                'side_effects' => 'Agranulocitosis (raro), hipotensión, reacciones alérgicas',
                'sku' => 'MED-MET-500',
                'available_quantity' => 120,
            ],
            [
                'name' => 'Ketorolaco 10mg',
                'description' => 'Antiinflamatorio no esteroideo para dolor postoperatorio',
                'salt_composition' => 'Ketorolac Tromethamine',
                'side_effects' => 'Dolor abdominal, náuseas, úlceras gástricas, sangrado',
                'sku' => 'MED-KET-010',
                'available_quantity' => 90,
            ],
            [
                'name' => 'Diclofenaco 50mg',
                'description' => 'Antiinflamatorio para dolor e inflamación postoperatoria',
                'salt_composition' => 'Diclofenac Sodium',
                'side_effects' => 'Gastritis, úlceras, dolor abdominal, mareos',
                'sku' => 'MED-DIC-050',
                'available_quantity' => 110,
            ],
            [
                'name' => 'Azitromicina 500mg',
                'description' => 'Antibiótico macrólido de amplio espectro',
                'salt_composition' => 'Azithromycin Dihydrate',
                'side_effects' => 'Diarrea, náuseas, dolor abdominal, cefalea',
                'sku' => 'MED-AZI-500',
                'available_quantity' => 80,
            ],
            [
                'name' => 'Metronidazol 500mg',
                'description' => 'Antibiótico para infecciones anaerobias',
                'salt_composition' => 'Metronidazole',
                'side_effects' => 'Náuseas, sabor metálico, cefalea, orina oscura',
                'sku' => 'MED-MET-500-A',
                'available_quantity' => 95,
            ],
            [
                'name' => 'Nimesulida 100mg',
                'description' => 'Antiinflamatorio con acción analgésica',
                'salt_composition' => 'Nimesulide',
                'side_effects' => 'Náuseas, gastritis, toxicidad hepática (raro)',
                'sku' => 'MED-NIM-100',
                'available_quantity' => 70,
            ],
            [
                'name' => 'Tramadol 50mg',
                'description' => 'Analgésico opioide para dolor moderado a severo',
                'salt_composition' => 'Tramadol Hydrochloride',
                'side_effects' => 'Mareos, náuseas, somnolencia, estreñimiento, dependencia',
                'sku' => 'MED-TRA-050',
                'available_quantity' => 60,
            ],
            [
                'name' => 'Cefalexina 500mg',
                'description' => 'Antibiótico cefalosporina de primera generación',
                'salt_composition' => 'Cephalexin',
                'side_effects' => 'Diarrea, náuseas, reacciones alérgicas, candidiasis',
                'sku' => 'MED-CEF-500',
                'available_quantity' => 85,
            ],
            [
                'name' => 'Dexametasona 4mg',
                'description' => 'Corticosteroide para inflamación severa',
                'salt_composition' => 'Dexamethasone',
                'side_effects' => 'Retención de líquidos, hiperglucemia, insomnio, cambios de humor',
                'sku' => 'MED-DEX-004',
                'available_quantity' => 50,
            ],
            [
                'name' => 'Prednisona 5mg',
                'description' => 'Corticosteroide antiinflamatorio',
                'salt_composition' => 'Prednisone',
                'side_effects' => 'Aumento de peso, hipertensión, hiperglucemia, osteoporosis',
                'sku' => 'MED-PRE-005',
                'available_quantity' => 65,
            ],
            [
                'name' => 'Benzocaína Gel 20%',
                'description' => 'Anestésico tópico para alivio del dolor dental',
                'salt_composition' => 'Benzocaine',
                'side_effects' => 'Irritación local, reacciones alérgicas, metahemoglobinemia (raro)',
                'sku' => 'MED-BEN-020',
                'available_quantity' => 40,
            ],
            [
                'name' => 'Lidocaína 2% con Epinefrina',
                'description' => 'Anestésico local para procedimientos dentales',
                'salt_composition' => 'Lidocaine Hydrochloride + Epinephrine',
                'side_effects' => 'Nerviosismo, mareos, taquicardia, convulsiones (sobredosis)',
                'sku' => 'MED-LID-002',
                'available_quantity' => 150,
            ],
            [
                'name' => 'Articaína 4% con Epinefrina',
                'description' => 'Anestésico local de acción rápida',
                'salt_composition' => 'Articaine Hydrochloride + Epinephrine',
                'side_effects' => 'Parestesia, dolor en sitio de inyección, taquicardia',
                'sku' => 'MED-ART-004',
                'available_quantity' => 130,
            ],
            [
                'name' => 'Omeprazol 20mg',
                'description' => 'Inhibidor de bomba de protones para protección gástrica',
                'salt_composition' => 'Omeprazole',
                'side_effects' => 'Cefalea, diarrea, dolor abdominal, deficiencia de B12',
                'sku' => 'MED-OME-020',
                'available_quantity' => 100,
            ],
            [
                'name' => 'Clorhexidina 0.12%',
                'description' => 'Enjuague bucal antiséptico',
                'salt_composition' => 'Chlorhexidine Gluconate',
                'side_effects' => 'Manchas en dientes, alteración del gusto, irritación oral',
                'sku' => 'MED-CLO-012',
                'available_quantity' => 200,
            ],
            [
                'name' => 'Ranitidina 150mg',
                'description' => 'Bloqueador H2 para protección gástrica',
                'salt_composition' => 'Ranitidine Hydrochloride',
                'side_effects' => 'Cefalea, mareos, estreñimiento, diarrea',
                'sku' => 'MED-RAN-150',
                'available_quantity' => 75,
            ],
        ];

        foreach ($medicines as $medicineData) {
            // Create the item first
            $item = Item::create([
                'name' => $medicineData['name'],
                'description' => $medicineData['description'],
                'category_id' => $medicineCategory->id,
                'brand_id' => $defaultBrand->id,
                'manufacturer_id' => $defaultManufacturer->id,
                'company_id' => $company->id,
                'sku' => $medicineData['sku'],
                'type' => 'medicine',
                'available_quantity' => $medicineData['available_quantity'],
                'alert_quantity' => 10,
                'unit' => 'ml',
            ]);

            // Create the medicine record
            Medicine::create([
                'item_id' => $item->id,
                'salt_composition' => $medicineData['salt_composition'],
                'side_effects' => $medicineData['side_effects'],
                'company_id' => $company->id,
            ]);

            $this->command->info("Medicina creada: {$medicineData['name']}");
        }

        $this->command->info('Seeder de medicinas completado exitosamente.');
    }
}
