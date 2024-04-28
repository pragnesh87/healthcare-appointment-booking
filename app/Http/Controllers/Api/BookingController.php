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

    public function cancelAppointment(Request $request)
    {
        $request->validate(['appointment_id' => ['required']]);
        $appointment = Appointment::where('id', $request->appointment_id)
            ->where('user_id', auth()->id())
            ->first();

        if ($appointment && $this->canCancelAppointment($appointment)) {
            $appointment->update(['status' => 'cancelled']);
            return sendSuccess([], 'Appointment Cancelled');
        }
        return sendError('Can not cancel appointment', status: 400);
    }

    private function canCancelAppointment($appointment)
    {
        $current_time = Carbon::now();
        $booking_time = Carbon::parse($appointment->date . " " . $appointment->start_time);
        $diff = (int)$current_time->diffInHours($booking_time);

        if ($diff < (int)config('app.booking_cancellable_hours')) {
            return false;
        }
        return true;
    }

    public function completeAppointment(Request $request)
    {
        $request->validate(['appointment_id' => ['required']]);
        $appointment = Appointment::where('id', $request->appointment_id)
            ->where('user_id', auth()->id())
            ->first();

        if ($appointment && $this->canCompleteAppointment($appointment)) {
            $appointment->update(['status' => 'completed']);
            return sendSuccess([], 'Appointment marked as complete.');
        }
        return sendError('Can not update appointment', status: 400);
    }

    private function canCompleteAppointment($appointment)
    {
        $current_time = Carbon::now();
        $booking_time = Carbon::parse($appointment->date . " " . $appointment->start_time);
        if ($current_time->gt($booking_time)) {
            return true;
        }
        return false;
    }
}
