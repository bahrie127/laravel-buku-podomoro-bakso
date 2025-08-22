<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'type' => $type,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 10000, 5000000),
            'note' => fake()->optional(0.7)->sentence(),
            'counterparty' => fake()->optional(0.5)->company(),
            'transfer_group_id' => null,
        ];
    }
}
