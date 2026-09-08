<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // أضف هذا السطر
class Ad extends Model
{
        use HasFactory;

    protected $fillable = ['title', 'image_path', 'link', 'is_active'];
}