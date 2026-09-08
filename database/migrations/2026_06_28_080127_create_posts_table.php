<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->string('image_path')->nullable();
        
        // سنربط المنشور بالمستخدم (الذي سيضيف المنشور)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // إضافة عمود الحالة للموافقة (افتراضياً false)
        $table->boolean('is_approved')->default(false); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
