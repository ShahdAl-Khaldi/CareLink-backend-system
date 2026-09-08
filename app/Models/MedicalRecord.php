<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // أضف هذا السطر
class MedicalRecord extends Model
{    use HasFactory;

    protected $fillable = [
        'patient_id', 'doctor_id', 'appointment_id',
        'record_type', 'diagnosis', 'notes', 'file_url', 'file_name',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
