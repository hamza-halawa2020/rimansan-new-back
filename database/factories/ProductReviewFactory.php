<?php

namespace Database\Factories;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductReview>
 */
class ProductReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'review' => $this->faker->paragraph,
            'rating' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
