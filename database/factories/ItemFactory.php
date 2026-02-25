<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'category_id' => null,
            'unit' => 'kg',
            'description' => $this->faker->sentence,
            'available_quantity' => $this->faker->numberBetween(1, 100),
            'company_id' => null,
            'sku' => $this->faker->unique()->ean8,
            'item_length' => $this->faker->randomFloat(2, 1, 100),
            'item_width' => $this->faker->randomFloat(2, 1, 100),
            'item_height' => $this->faker->randomFloat(2, 1, 100),
            'dimension_unit' => 'cm',
            'weight' => $this->faker->randomFloat(2, 1, 100),
            'weight_unit' => 'kg',
            'manufacturer_id' => null,
            'brand_id' => null,
        ];
    }
}
