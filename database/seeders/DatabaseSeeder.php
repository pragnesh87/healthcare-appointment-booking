<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\HealthcareProfessional;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        HealthcareProfessional::factory(10)->create();
        Appointment::factory(30)->create();
    }
}
