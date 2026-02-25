<?php

namespace Database\Factories;

use App\Models\ItemBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemBrandFactory extends Factory
{
    protected $model = ItemBrand::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'is_active' => true,
            'company_id' => null,
        ];
    }
}
