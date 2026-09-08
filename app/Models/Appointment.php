<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'patient_id', 'patient_type', 'scheduled_at', 'duration_minutes',
        'type', 'status', 'description', 'fees', 'meeting_link', 'cancellation_reason',
        'diagnosis', 'clinical_notes', 'lab_tests', 'lab_status', 'medications',
    ];

    public function patient()
    {
        return $this->morphTo();
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

   // public function prescription(): HasOne
    //{
     //   return $this->hasOne(Prescription::class);
   // }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

  //  public function ratings()
   // {
    //    return $this->hasMany(DoctorRating::class, 'doctor_id');
   // }

    public function rating()
    {
        return $this->hasOne(DoctorRating::class, 'appointment_id');
    }

    // ملاحظة: "متوسط التقييم" منطقياً حق الطبيب (كل مواعيده مع بعض)، مش الموعد
    // الواحد (يلي إله تقييم واحد بس عن طريق rating() فوق). النسخة الصح موجودة
    // بموديل User::getAverageRatingAttribute() - كانت هون بتنادي ratings() يلي
    // انحذفت (مبقاش عندها Appointment doctor_id) فكانت رح تطلع خطأ لو استخدمت


    // أضف هذه العلاقات داخل نموذج Appointment
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'appointment_id');
    }

    public function imagingOrders()
    {
        return $this->hasMany(ImagingOrder::class, 'appointment_id');
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'appointment_id');
    }
}
