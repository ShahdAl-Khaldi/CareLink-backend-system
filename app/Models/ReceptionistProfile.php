<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceptionistProfile extends Model
{
    protected $fillable = ['user_id', 'shift_start', 'shift_end', 'desk_number'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
