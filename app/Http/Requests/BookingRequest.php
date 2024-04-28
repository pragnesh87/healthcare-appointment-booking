<?php

namespace App\Http\Requests;

use App\Rules\FutureBookingDateTime;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'healthcare_professional_id' => ['required'],
            'date' => ['required', 'date_format:Y-m-d', 'after:yesterday'],
            'start_time' => ['required', 'date_format:H:i:s', new FutureBookingDateTime(config('app.booking_buffer_time_in_minute'))],
        ];
    }
}
