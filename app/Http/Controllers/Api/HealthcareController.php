<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthcareProfessional;
use Illuminate\Http\Request;

class HealthcareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $healthcares = HealthcareProfessional::select(['name', 'specialty'])
            ->paginate()
            ->toArray();
        return sendSuccess($healthcares);
    }
}
