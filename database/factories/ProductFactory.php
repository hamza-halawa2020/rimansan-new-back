<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $priceBeforeDiscount = $this->faker->randomFloat(2, 50, 500);
        $discount = $this->faker->numberBetween(5, 30);
        $priceAfterDiscount = $priceBeforeDiscount - ($priceBeforeDiscount * ($discount / 100));

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'stock' => $this->faker->numberBetween(0, 100),
            'priceBeforeDiscount' => $priceBeforeDiscount,
            'discount' => $discount,
            'priceAfterDiscount' => round($priceAfterDiscount, 2),
            'category_id' => Category::factory(),
            'admin_id' => User::factory(),
        ];
    }
}
