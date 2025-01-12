<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Client;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admin_id' => User::factory(),
            'client_id' => Client::factory(),
            'coupon_id' => Coupon::factory(),
            'total_price' => $this->faker->randomFloat(2, 50, 1000),
            'notes' => $this->faker->optional()->sentence(),
            'payment_method' => $this->faker->randomElement(['cash_on_delivery', 'visa', 'vodafone_cash']),
            'status' => $this->faker->randomElement(['Pending', 'Canceled', 'Delivered']),
        ];
    }
}
