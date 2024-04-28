<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Appointment;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\HealthcareProfessional;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_show_unauthentication_error_if_user_try_to_book_with_invalid_token()
    {
        $this->withHeader('Authorization', 'Bearer some-random-token')
            ->postJson(route('appointment.book'))
            ->assertStatus(401);
    }
    public function test_show_validation_error_if_healthcare_professional_id_is_missing()
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => null,
                'date' => Carbon::parse($booking_date)->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['healthcare_professional_id']);
    }

    public function test_show_validation_error_if_date_format_is_incorrect()
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => 1,
                'date' => Carbon::parse($booking_date)->format('d-m-Y'),
                'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_show_validation_error_if_start_time_format_is_incorrect()
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => 1,
                'date' => Carbon::parse($booking_date)->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->format('H:i'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    public function test_show_invalid_booking_if_past_date_selected(): void
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => 1,
                'date' => Carbon::parse($booking_date)->subDay()->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_show_invalid_booking_if_past_start_time_selected(): void
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => 1,
                'date' => Carbon::parse($booking_date)->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->subMinutes(60)->format('H:i:s'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    public function test_book_an_appointment(): void
    {
        $this->seed();
        $token = $this->getValidLoginToken();
        $healthcares = HealthcareProfessional::all()->pluck('id');

        $booking_date = Carbon::now()->addMinutes(40);
        $response = $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => fake()->randomElement($healthcares),
                'date' => Carbon::parse($booking_date)->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            ])
            ->assertStatus(201);
    }

    public function test_should_not_allow_duplicate_booking_for_a_healthcare(): void
    {
        $this->seed();
        $token = $this->getValidLoginToken();
        $healthcares = HealthcareProfessional::all()->pluck('id');

        $booking_date = Carbon::now()->addMinutes(40);
        $healthcare_id = fake()->randomElement($healthcares);
        $data = [
            'healthcare_professional_id' => $healthcare_id,
            'date' => Carbon::parse($booking_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
        ];
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $data)
            ->assertStatus(201);

        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $data)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => [],
                'message' => 'Booking slot not available please choose different time slot'
            ]);
    }

    public function test_should_not_allow_conflicting_booked_slot_for_a_healthcare(): void
    {
        $this->seed();
        $token = $this->getValidLoginToken();
        $healthcares = HealthcareProfessional::all()->pluck('id');

        $healthcare_id = fake()->randomElement($healthcares);
        $booking_slot = Carbon::now()->addMinutes(40);
        $booking_slot2 = Carbon::now()->addMinutes(50);
        $slot1 = [
            'healthcare_professional_id' => $healthcare_id,
            'date' => Carbon::parse($booking_slot)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_slot)->format('H:i:s'),
        ];
        $slot2 = [
            'healthcare_professional_id' => $healthcare_id,
            'date' => Carbon::parse($booking_slot2)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_slot2)->format('H:i:s'),
        ];

        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $slot1)
            ->assertStatus(201);

        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $slot2)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => [],
                'message' => 'Booking slot not available please choose different time slot'
            ]);
    }
    public function test_can_book_second_appointment_after_booked_slot(): void
    {
        $this->seed();
        $token = $this->getValidLoginToken();
        $healthcares = HealthcareProfessional::all()->pluck('id');

        $healthcare_id = fake()->randomElement($healthcares);
        $booking_slot = Carbon::now()->addMinutes(35);
        $booking_slot2 = Carbon::now()->addMinutes(66);
        $slot1 = [
            'healthcare_professional_id' => $healthcare_id,
            'date' => Carbon::parse($booking_slot)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_slot)->format('H:i:s'),
        ];
        $slot2 = [
            'healthcare_professional_id' => $healthcare_id,
            'date' => Carbon::parse($booking_slot2)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_slot2)->format('H:i:s'),
        ];

        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $slot1)
            ->assertStatus(201);

        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), $slot2)
            ->assertStatus(201);
    }

    public function test_can_cancel_an_appointment()
    {
        $this->seed();
        $token = $this->getValidLoginToken();

        $booking_date = Carbon::now()->addDays(2);
        $appointment = Appointment::factory()->create([
            'user_id' => auth()->id(),
            'date' => Carbon::parse($booking_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            'end_time' => Carbon::parse($booking_date)->addMinutes((int)config('app.booking_time_slot'))->format('H:i:s'),
            'status' => 'booked'
        ]);

        $this->withHeader('Authorization', $token)
            ->postJson(route('cancel.appointment'), [
                'appointment_id' => $appointment->id
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'cancelled']);
    }

    public function test_cannot_cancel_appointment_within_24_hours_of_schedule_time()
    {
        $this->seed();
        $token = $this->getValidLoginToken();

        $booking_date = Carbon::now()->addHours(20);
        $appointment = Appointment::factory()->create([
            'user_id' => auth()->id(),
            'date' => Carbon::parse($booking_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            'end_time' => Carbon::parse($booking_date)->addMinutes((int)config('app.booking_time_slot'))->format('H:i:s'),
            'status' => 'booked'
        ]);

        $this->withHeader('Authorization', $token)
            ->postJson(route('cancel.appointment'), [
                'appointment_id' => $appointment->id
            ])
            ->assertStatus(400);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'booked']);
    }

    public function test_can_mark_past_appointment_as_complete()
    {
        $this->seed();
        $token = $this->getValidLoginToken();

        $booking_date = Carbon::now()->subDay();
        $appointment = Appointment::factory()->create([
            'user_id' => auth()->id(),
            'date' => Carbon::parse($booking_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            'end_time' => Carbon::parse($booking_date)->addMinutes((int)config('app.booking_time_slot'))->format('H:i:s'),
            'status' => 'booked'
        ]);

        $this->withHeader('Authorization', $token)
            ->postJson(route('complete.appointment'), [
                'appointment_id' => $appointment->id
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
    }

    public function test_cannot_mark_future_appointment_as_complete()
    {
        $this->seed();
        $token = $this->getValidLoginToken();

        $booking_date = Carbon::now()->addDay();
        $appointment = Appointment::factory()->create([
            'user_id' => auth()->id(),
            'date' => Carbon::parse($booking_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
            'end_time' => Carbon::parse($booking_date)->addMinutes((int)config('app.booking_time_slot'))->format('H:i:s'),
            'status' => 'booked'
        ]);

        $this->withHeader('Authorization', $token)
            ->postJson(route('cancel.appointment'), [
                'appointment_id' => $appointment->id
            ])
            ->assertStatus(400);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'booked']);
    }
}
