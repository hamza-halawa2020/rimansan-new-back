<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AddSideBarBanner>
 */
class AddSideBarBannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => fake()->imageUrl(),
            'link' => fake()->url(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'admin_id' => User::factory(),
        ];
    }
}
