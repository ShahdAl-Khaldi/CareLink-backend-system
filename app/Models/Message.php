<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'sender_id', 'sender_type',
        'body', 'attachment_path', 'attachment_type',
    ];

    protected $appends = ['attachment_url'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

   public function getAttachmentUrlAttribute()
{
    return $this->attachment_path ? url(Storage::url($this->attachment_path)) : null;
}
}