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
    Schema::table('appointments', function (Blueprint $table) {
        $table->text('diagnosis')->nullable();
        $table->text('clinical_notes')->nullable();
        $table->text('lab_tests')->nullable();
        $table->string('lab_status')->default('pending'); // pending, completed
        $table->text('medications')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            //
        });
    }
};
