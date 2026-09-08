<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model {
    // كانت ناقصة date_of_birth/address/gender/national_id/profile_picture رغم وجودهم
    // فعلياً بجدول doctor_profiles - فكانوا يترفضوا بصمت من أي update() بدون أي خطأ ظاهر
    protected $fillable = [
        'user_id',
        'specialty',
        'clinic_address',
        'years_of_experience',
        'status',
        'date_of_birth',
        'address',
        'gender',
        'national_id',
        'profile_picture',
        'credential_document',
    ];
    public function user() { return $this->belongsTo(User::class); }
}