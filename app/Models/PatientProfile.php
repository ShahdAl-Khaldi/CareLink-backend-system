<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // أضف هذا السطر

class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'blood_type', 'weight_kg', 'height_cm',
        'is_diabetic', 'is_hypertensive', 'is_smoker', 'allergies',
        'chronic_diseases', 'current_medications',
        'emergency_contact_name', 'emergency_contact_phone',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
