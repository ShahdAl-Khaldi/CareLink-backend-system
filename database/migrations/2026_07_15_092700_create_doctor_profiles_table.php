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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialty');
            $table->string('clinic_address')->nullable(); // اجعله nullable            $table->integer('years_of_experience');
            $table->timestamps();

            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('national_id')->unique()->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->enum('gender', ['male', 'female']);
            $table->string('credential_document')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
