<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabProfile extends Model
{
    protected $fillable = ['user_id', 'certification_number', 'lab_section'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
