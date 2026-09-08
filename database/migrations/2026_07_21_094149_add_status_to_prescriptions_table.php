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
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->string('status')->default('pending'); // pending, ready, dispensed
        $table->timestamp('dispensed_at')->nullable();
    });
}

public function down()
{
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->dropColumn(['status', 'dispensed_at']);
    });
}
};
