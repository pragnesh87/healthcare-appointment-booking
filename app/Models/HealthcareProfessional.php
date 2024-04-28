<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthcareProfessional extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialty'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'healthcare_professional_id');
    }
}
