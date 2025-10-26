<?php

namespace Database\Factories;


use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        $openingTime = $this->faker->dateTimeBetween('08:00', '12:00');
        $closingTime = (clone $openingTime)->modify('+'.rand(1,4).' hours');

        return [
            'title'         => $this->faker->sentence(3),
            'description'   => $this->faker->paragraph(),
            'location'      => $this->faker->address(),
            'type_meeting'  => $this->faker->randomElement(['virtual', 'in-person', 'hybrid']),
            'date'          => $this->faker->date(),
            'opening_time'  => $openingTime,
            'closing_time'  => $closingTime,
            'status'        => $this->faker->randomElement(['open', 'closed']),
            'attachments'   => [], // Inicialmente vacío
            'created_by'    => \App\Models\User::factory(),
        ];
    }
}
