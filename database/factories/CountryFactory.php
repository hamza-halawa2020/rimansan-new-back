<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $countries = [
        'Egypt',
        'United States',
        'Canada',
        'Germany',
        'France',
        'Australia',
        'India',
        'Brazil',
        'China',
        'Japan'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($countries),
        ];

        //                 INSERT INTO countries (name) 
// VALUES 
// ('Algeria'),
// ('Bahrain'),
// ('Comoros'),
// ('Djibouti'),
// ('Egypt'),
// ('Iraq'),
// ('Jordan'),
// ('Kuwait'),
// ('Lebanon'),
// ('Libya'),
// ('Mauritania'),
// ('Morocco'),
// ('Oman'),
// ('Palestine'),
// ('Qatar'),
// ('Saudi Arabia'),
// ('Somalia'),
// ('Sudan'),
// ('Syria'),
// ('Tunisia'),
// ('United Arab Emirates'),
// ('Yemen');


    }
}
