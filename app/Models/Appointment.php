<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'healthcare_professional_id', 'date', 'start_time', 'end_time', 'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function heathcareProfessional(): BelongsTo
    {
        return $this->belongsTo(HealthcareProfessional::class);
    }
}
