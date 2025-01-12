<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $egyptianCities = [
        'Cairo',
        'Alexandria',
        'Giza',
        'Shubra El-Kheima',
        'Port Said',
        'Suez',
        'El Mahalla El Kubra',
        'Luxor',
        'Mansoura',
        'Tanta',
        'Asyut',
        'Ismailia',
        'Faiyum',
        'Zagazig',
        'Damietta',
        'Aswan',
        'Minya',
        'Damanhur',
        'Beni Suef',
        'Qena',
        'Sohag',
        'Hurghada',
        '6th of October City',
        'Sharm El Sheikh',
        'Banha',
        'Arish',
        '10th of Ramadan City',
        'Marsa Matruh',
        'Banha',
        'Al Khankah'
        ];
        return [
            'name' => $this->faker->unique(true, 10000)->city(),
            'country_id' => 1,
        ];

    }
}



// INSERT INTO cities (name, country_id) 
// VALUES 
// ('Cairo', 5),
// ('Alexandria', 5),
// ('Giza', 5),
// ('Shubra El Kheima', 5),
// ('Port Said', 5),
// ('Suez', 5),
// ('Luxor', 5),
// ('Aswan', 5),
// ('Ismailia', 5),
// ('Tanta', 5),
// ('Mansoura', 5),
// ('Damanhur', 5),
// ('Zagazig', 5),
// ('Fayoum', 5),
// ('Hurghada', 5),
// ('El-Mahalla El-Kubra', 5),
// ('Qena', 5),
// ('Sohag', 5),
// ('El-Arish', 5),
// ('Damietta', 5),
// ('Sidi Barrani', 5),
// ('Sheikh Zuweid', 5),
// ('Rafah', 5),
// ('Asyut', 5),
// ('Beni Suef', 5),
// ('Badr City', 5),
// ('6th of October City', 5),
// ('Sharm El-Sheikh', 5),
// ('Port Fouad', 5),
// ('Kafr El Sheikh', 5);

