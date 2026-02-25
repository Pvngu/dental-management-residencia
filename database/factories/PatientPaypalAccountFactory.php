<?php

namespace Database\Factories;

use App\Models\PatientPaypalAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientPaypalAccountFactory extends Factory
{
    protected $model = PatientPaypalAccount::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'is_default' => false,
        ];
    }
}
