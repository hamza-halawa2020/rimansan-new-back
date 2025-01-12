<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
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
            'order_id' => Order::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'total' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
