<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftHandover extends Model
{
    // author_id/acknowledged_by بيتحطوا من الباك إند دايماً (auth()->id())، مش من الفرونت،
    // عشان محدا يقدر ينتحل اسم موظف تاني بس لأنه عرف يبعت الحقل بالـ request
    protected $fillable = [
        'message',
        'author_id',
        'author_name',
        'acknowledged',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
