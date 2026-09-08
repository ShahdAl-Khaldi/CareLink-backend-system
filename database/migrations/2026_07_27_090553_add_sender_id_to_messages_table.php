<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (!Schema::hasColumn('messages', 'sender_id')) {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')
                ->after('conversation_id')
                ->constrained('users')
                ->onDelete('cascade');
        });
    }
}

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropColumn('sender_id');
        });
    }
};