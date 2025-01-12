<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MainSlider>
 */
class MainSliderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(2),
            'image' => $this->faker->imageUrl(1920, 1080, 'nature', true, 'Slider'),
            'link' => $this->faker->url(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'admin_id' => User::factory(),
        ];
    }
}
