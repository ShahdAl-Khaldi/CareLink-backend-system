<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // أضف هذا السطر
class Post extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'image_path', 'user_id', 'is_approved'];

    
   // public function user() {
  //      return $this->belongsTo(User::class, 'admin_id');
  //  }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
