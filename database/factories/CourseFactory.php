<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Instructor;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_id' => User::factory(),
            'category_id' => Category::factory(),
            'tag_id' => Tag::factory(),
            'instructor_id' => Instructor::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'video_url' => fake()->url(),
            'image' => fake()->imageUrl(640, 480, 'course', true, 'Course Image'),
            'price' => fake()->randomFloat(2, 50, 500),
            'certifications' => fake()->boolean()
        ];
    }
}
