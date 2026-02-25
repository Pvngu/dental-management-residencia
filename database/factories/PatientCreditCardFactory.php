<?php

namespace Database\Factories;

use App\Models\PatientCreditCard;
use App\Models\Patient;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientCreditCardFactory extends Factory
{
    protected $model = PatientCreditCard::class;

    public function definition()
    {
        // Valid card types that match both Faker and our system
        $cardTypes = [
            ['type' => 'visa', 'faker' => 'Visa'],
            ['type' => 'mastercard', 'faker' => 'MasterCard'],
            ['type' => 'amex', 'faker' => 'American Express'],
        ];
        
        $selectedCard = $this->faker->randomElement($cardTypes);
        
        // Generate a date in the future (1-5 years)
        $expiryDate = now()->addYears(rand(1, 5));
        
        return [
            'card_number' => $this->faker->creditCardNumber($selectedCard['faker']),
            'expiry_month' => $expiryDate->format('m'),
            'expiry_year' => $expiryDate->format('Y'),
            'cvc' => str_pad($this->faker->numberBetween(100, 999), 3, '0', STR_PAD_LEFT),
            'card_type' => $selectedCard['type'],
            'name_on_card' => $this->faker->name(),
            'is_default' => false,
        ];
    }

    public function default()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }
}
