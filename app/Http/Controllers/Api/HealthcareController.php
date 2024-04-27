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
            ->get()
            ->toArray();
        return sendSuccess($healthcares);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
