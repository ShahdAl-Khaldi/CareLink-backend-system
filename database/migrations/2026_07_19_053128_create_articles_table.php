<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // ضروري لمحركات البحث
            $table->string('category');
            $table->string('author');
            $table->text('excerpt'); // المقتطف
            $table->longText('content')->nullable(); // محتوى المقال الكامل
            $table->string('image')->nullable(); // مسار الصورة
            $table->enum('status', ['published', 'draft'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
