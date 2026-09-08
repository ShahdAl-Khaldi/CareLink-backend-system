<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['appointment_id', 'doctor_id', 'patient_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // بيتأكد إذا المستخدم طرف بهاي المحادثة
    public function hasParticipant(int $userId): bool
    {
        return $this->doctor_id === $userId || $this->patient_id === $userId;
    }

    // القفل حسب حالة الموعد - عدّل القيم حسب الـ status الموجود عندك فعلياً
    public function isLocked(): bool
    {
        return in_array($this->appointment->status, ['completed', 'cancelled']);
    }
}