<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $firstNames = $this->faker->firstName().(rand(0, 1) ? ' '.$this->faker->firstName() : '');
        $lastNames = $this->faker->lastName().(rand(0, 1) ? ' '.$this->faker->lastName() : '');

        return [
            'first_name' => $firstNames,
            'last_name' => $lastNames,
            'dni' => $this->faker->unique()->numerify('########'), // 8 dígitos
            'phone' => $this->faker->numerify('9########'),          // Perú 9 dígitos
            'email' => $this->faker->unique()->safeEmail(),
            'organization' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
