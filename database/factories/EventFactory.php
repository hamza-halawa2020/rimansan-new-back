<?php

namespace Database\Factories;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5), // Generate a random title with 5 words
            'content' => fake()->paragraph(3), // Generate a random paragraph with 3 sentences
            'image' => fake()->imageUrl(640, 480, 'event', true, 'Event Image'), // Generate a random image URL
            'admin_id' => User::factory(), // Associate with a randomly created admin user
            'category_id' => Category::factory(), // Associate with a randomly created category
            'tag_id' => Tag::factory(), // Associate with a randomly created tag
        ];
    }
}
