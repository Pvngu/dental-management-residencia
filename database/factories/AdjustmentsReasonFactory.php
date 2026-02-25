<?php

namespace Database\Factories;

use App\Models\AdjustmentsReason;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdjustmentsReasonFactory extends Factory
{
    protected $model = AdjustmentsReason::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'company_id' => null,
            'is_active' => true,
        ];
    }
}
