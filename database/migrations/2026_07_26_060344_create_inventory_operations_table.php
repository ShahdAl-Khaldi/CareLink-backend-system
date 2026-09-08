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
        Schema::create('inventory_operations', function (Blueprint $table) {
            $table->id();

            // nullOnDelete: عشان يضل سجل العملية موجود حتى لو انحذف الصنف نهائياً
            $table->foreignId('inventory_id')
                ->nullable()
                ->constrained('inventories')
                ->nullOnDelete();

            // اسم الصنف مخزَّن بشكل منفصل (denormalized) عشان يضل ظاهر بعد الحذف
            $table->string('item_name');

            // create | update | delete | restock | adjust
            $table->string('type');

            $table->integer('delta')->default(0);
            $table->string('actor_name')->nullable();
            $table->string('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_operations');
    }
};
