<?php

 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'tests',
        'sample_id',
        'clinical_reason',
        'notes',
        'priority',
        'status',
        'result_text',
        'completed_by',
        'completed_at',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // اسمها مش patient() قصداً (زي ImagingOrder) - مش علاقة Eloquent حقيقية،
    // المريض ممكن يكون User (حجز بنفسه) أو Patient (سجله الاستقبال) حسب الموعد
    public function resolvePatient()
    {
        return $this->appointment?->patient;
    }

    // علاقة الطبيب
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}