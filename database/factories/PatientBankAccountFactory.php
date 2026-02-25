<?php

namespace Database\Factories;

use App\Models\PatientBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientBankAccountFactory extends Factory
{
    protected $model = PatientBankAccount::class;

    public function definition(): array
    {
        $accountTypes = ['checking', 'savings'];
        $banks = [
            'Bank of America',
            'Wells Fargo',
            'Chase Bank',
            'Citibank',
            'US Bank',
            'PNC Bank',
            'Capital One',
            'TD Bank',
            'BB&T',
            'SunTrust Bank'
        ];

        return [
            'bank_name' => $this->faker->randomElement($banks),
            'account_number' => $this->faker->numerify('##########'),
            'routing_number' => $this->faker->numerify('#########'),
            'account_type' => $this->faker->randomElement($accountTypes),
            'account_holder_name' => $this->faker->name(),
            'is_default' => false,
        ];
    }
}
