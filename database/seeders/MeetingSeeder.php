<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $default = rand(10, 30);
        $count = (int) env('SEED_MEETINGS_COUNT', $default);

        Meeting::factory()->count($count)->create();
    }
}
