<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ParticipantSeeder::class,
            // MeetingSeeder::class,
            // AttendanceSeeder::class,
        ]);
        // User::firstOrCreate(
        //     ['email' => 'admin@admin.com'],
        //     [
        //         'name' => 'Administrador',
        //         'password' => Hash::make('password'),
        //         'role' => 'Administrator',
        //     ]
        // );

        // User::firstOrCreate(
        //     ['email' => 'secretary@admin.com'],
        //     [
        //         'name' => 'Secretario',
        //         'password' => Hash::make('password'),
        //         'role' => 'Secretary',
        //     ]
        // );
    }
}




