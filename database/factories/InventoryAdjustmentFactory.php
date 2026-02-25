<?php

namespace Database\Factories;

use App\Models\InventoryAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryAdjustmentFactory extends Factory
{
    protected $model = InventoryAdjustment::class;

    public function definition()
    {
        return [
            'reference_number' => $this->faker->unique()->numerify('REF-###'),
            'date' => $this->faker->date(),
            'adjustments_reason_id' => null,
            'description' => null,
            'company_id' => null,
        ];
    }
}
