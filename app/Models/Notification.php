<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'body', 'appointment_id', 'is_read', 'notifiable_id', 'notifiable_type'];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // كل إشعارات مستخدم معين (باستخدام User دايماً حسب النظام الموحد عندك)
    public function scopeForUser($query, int $userId)
    {
        return $query->where('notifiable_id', $userId)->where('notifiable_type', User::class);
    }
}