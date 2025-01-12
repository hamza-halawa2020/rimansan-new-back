<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('COUPON-####'), // Generates a unique coupon code
            'name' => fake()->words(2, true), // Generates a random name
            'description' => fake()->sentence(), // Generates a random description
            'discount' => fake()->randomFloat(2, 5, 50), // Random discount between 5 and 50
            'max_uses' => fake()->numberBetween(10, 100), // Random max uses between 10 and 100
            'uses_count' => 0, // Defaults to 0 for new coupons
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'), // Random date in range
            'end_date' => fake()->dateTimeBetween('+1 month', '+3 months'), // End date after start date
            'is_active' => fake()->boolean(), // Random true/false
            'admin_id' => User::factory(), // Associates with a User model
        ];
    }
}
