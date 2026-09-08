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
    Schema::create('inventories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('quantity')->default(0);
        $table->integer('min_quantity')->default(10);
        $table->string('unit')->default('علبة');
        $table->text('keywords')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
