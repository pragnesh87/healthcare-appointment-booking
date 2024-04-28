<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function book(BookingRequest $request)
    {
        $data = $request->validated();
        $minutes = (int)config('app.booking_time_slot');
        $data['user_id'] = auth()->id();
        $data['end_time'] = Carbon::parse($data['date'] . ' ' . $data['start_time'])->addMinutes($minutes)->format('H:i:s');
        if (
            $this->isBookingSlotAvailable($data)
            && $this->isConflictWithBookedSlot($data)
        ) {
            $appointment = Appointment::create($data);
            return sendSuccess($appointment->toArray(), 'Appointment Booked successfully.', Response::HTTP_CREATED);
        }
        return sendError('Booking slot not available please choose different time slot', status: Response::HTTP_BAD_REQUEST);
    }


    /**
     * check if booking date and time not already booked
     *
     * @param array $data
     * @return boolean
     */
    private function isBookingSlotAvailable(array $data): bool
    {
        return !Appointment::where('healthcare_professional_id', $data['healthcare_professional_id'])
            ->where('date', $data['date'])
            ->where('start_time', $data['start_time'])
            ->where('status', 'booked')
            ->exists();
    }

    /**
     * check if booking start time and end time
     * does not conflict with other booking time.
     * @param mixed $data
     * @return bool
     */
    private function isConflictWithBookedSlot($data)
    {
        return !Appointment::where('healthcare_professional_id', $data['healthcare_professional_id'])
            ->where('date', $data['date'])
            ->where('status', 'booked')
            ->whereRaw("('" . $data['start_time'] . "' between `start_time` and `end_time` OR '" . $data['end_time'] . "' between `start_time` and `end_time`)")
            ->exists();
    }
}
