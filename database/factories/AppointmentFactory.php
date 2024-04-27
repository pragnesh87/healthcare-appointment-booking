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
        $randomMinutes = fake()->randomElement(['00', '30']);
        $randomHours = fake()->numberBetween(9, 21);
        $date = fake()->dateTimeBetween('-1 year', '+0 days')->format('Y-m-d ' . $randomHours . ':' . $randomMinutes . ':00');
        $end_time = Carbon::parse($date)->addMinutes(30)->format('H:i:s');
        return [
            'user_id' => fake()->randomElement($users),
            'healthcare_professional_id' => fake()->randomElement($healthcare_professionals),
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'start_time' => Carbon::parse($date)->format('H:i:s'),
            'end_time' => $end_time,
            'status' => fake()->randomElement(['booked', 'completed', 'cancelled'])
        ];
    }
}
