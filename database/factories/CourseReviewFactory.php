<?php

namespace Database\Factories;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseReview>
 */
class CourseReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'review' => fake()->paragraph(3),
            'rating' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
