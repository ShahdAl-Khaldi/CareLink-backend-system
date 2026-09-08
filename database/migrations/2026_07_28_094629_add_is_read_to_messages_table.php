<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (!Schema::hasColumn('messages', 'is_read')) {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('attachment_type');
        });
    }
}

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};