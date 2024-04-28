<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function appointments()
    {
        $appointments = Appointment::with([
            'healthcareProfessional'
        ])
            ->where('user_id', auth()->id())
            ->where('status', 'booked')
            ->get()
            ->transform(function ($row) {
                return [
                    'appointment_id' => $row->id,
                    'date' => $row->date,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'healthcare_professional_name' => $row->healthcareProfessional->name,
                    'healthcare_professional_specialty' => $row->healthcareProfessional->specialty,
                ];
            });

        return paginate($appointments, 20);
    }
}
