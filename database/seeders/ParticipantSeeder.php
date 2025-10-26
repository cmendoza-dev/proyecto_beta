<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participant;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $default = rand(50, 100);
        $count = (int) env('SEED_PARTICIPANTS_COUNT', $default);

        Participant::factory()->count($count)->create();
    }
}
