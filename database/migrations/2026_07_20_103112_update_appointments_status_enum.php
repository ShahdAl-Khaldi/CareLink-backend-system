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
        $table->enum('status', [
            'pending', 
            'confirmed', 
            'with_doctor', 
            'awaiting_lab', 
            'awaiting_pharmacy', 
            'completed', 
            'cancelled'
        ])->default('pending')->change();
    });
}

public function down()
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
              ->default('pending')->change();
    });
}
};
