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
    Schema::create('backups', function (Blueprint $table) {
        $table->id();
        $table->string('filename');
        $table->string('disk')->default('local');
        $table->unsignedBigInteger('size_bytes')->nullable();
        $table->enum('status', ['success', 'failed'])->default('success');
        $table->enum('type', ['manual', 'automatic'])->default('automatic');
        $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
