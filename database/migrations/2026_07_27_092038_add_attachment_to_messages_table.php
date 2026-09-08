<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('messages', function (Blueprint $table) {
        if (!Schema::hasColumn('messages', 'attachment_path')) {
            $table->string('attachment_path')->nullable()->after('body');
        }
        if (!Schema::hasColumn('messages', 'attachment_type')) {
            $table->enum('attachment_type', ['image', 'video'])->nullable()->after('attachment_path');
        }
        $table->text('body')->nullable()->change();
    });
}

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_type']);
            $table->text('body')->nullable(false)->change();
        });
    }
};