<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagingOrder extends Model
{
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'studies',
        'modality',
        'anatomy',
        'priority',
        'clinical_reason',
        'notes',
        'status',
        'result_text',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // اسمها مش patient() قصداً (مش علاقة Eloquent حقيقية، بترجع Model مباشرة)
    // عشان ما تتلخبط مع نظام العلاقات السحري لو انكتبت غلط كـ $order->patient
    public function resolvePatient()
    {
        return $this->appointment?->patient;
    }
}
