<?php

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
                'end_time' => Carbon::parse($booking_date)->addMinutes(30)->format('H:i:s')
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
                'end_time' => Carbon::parse($booking_date)->addMinutes(30)->format('H:i:s')
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
                'end_time' => Carbon::parse($booking_date)->addMinutes(30)->format('H:i:s')
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }

    public function test_show_validation_error_if_end_time_is_before_start_time()
    {
        $token = $this->getValidLoginToken();
        $booking_date = Carbon::now();
        $this->withHeader('Authorization', $token)
            ->postJson(route('appointment.book'), [
                'healthcare_professional_id' => 1,
                'date' => Carbon::parse($booking_date)->format('Y-m-d'),
                'start_time' => Carbon::parse($booking_date)->format('H:i:s'),
                'end_time' => Carbon::parse($booking_date)->subMinutes(30)->format('H:i:s')
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
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
                'end_time' => Carbon::parse($booking_date)->addMinutes(30)->format('H:i:s')
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
                'end_time' => Carbon::parse($booking_date)->addMinutes(30)->format('H:i:s')
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time']);
    }
}
