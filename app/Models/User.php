<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. تأكد من هذا الـ use

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'national_id', 'phone', 'birth_date', 'gender', 'address', 'profile_picture', 'department', 'specialty', 'credential_document', 'must_change_password'];

    protected $casts = ['must_change_password' => 'boolean'];

    protected $hidden = ['password', 'remember_token'];

    // العلاقات
    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function patientProfile()
    {
        return $this->hasOne(PatientProfile::class);
    }

    public function receptionistProfile()
    {
        return $this->hasOne(ReceptionistProfile::class);
    }

    public function labProfile()
    {
        return $this->hasOne(LabProfile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function ratings()
    {
        return $this->hasMany(DoctorRating::class, 'doctor_id');
    }

    // حساب متوسط التقييم تلقائياً للطبيب
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ? number_format($this->ratings()->avg('rating'), 1) : 0;
    }
}
