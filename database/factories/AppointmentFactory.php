<?php

namespace Database\Factories;

use App\Models\HealthcareProfessional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::all()->pluck('id');
        $healthcare_professionals = HealthcareProfessional::all()->pluck('id');
        $start_time = fake()->dateTime();
        $end_time = Carbon::parse($start_time)->addMinutes(30);
        return [
            'user_id' => fake()->randomElement($users),
            'healthcare_professional_id' => fake()->randomElement($healthcare_professionals),
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status' => fake()->randomElement(['booked', 'completed', 'cancelled'])
        ];
    }
}
