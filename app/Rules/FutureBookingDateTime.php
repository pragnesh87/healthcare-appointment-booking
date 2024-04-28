<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FutureBookingDateTime implements ValidationRule
{
    public function __construct(private int $buffer_time = 0)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $booking_date = request('date') . " " . $value;
        if ($this->buffer_time > 0) {
            if (Carbon::parse($booking_date)->lte(Carbon::now()->addMinutes($this->buffer_time))) {
                $fail(__('Booking time should be future time'));
            }
        } else if (Carbon::parse($booking_date)->lte(Carbon::now())) {
            $fail(__('Booking time should be future time'));
        }
    }
}
