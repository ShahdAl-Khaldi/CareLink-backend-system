<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'insurance_status')) {
                $table->string('insurance_status')->default('unknown')->after('address');
            }
            if (!Schema::hasColumn('patients', 'insurance_provider')) {
                $table->string('insurance_provider')->nullable()->after('insurance_status');
            }
            if (!Schema::hasColumn('patients', 'reception_flags')) {
                $table->json('reception_flags')->nullable()->after('insurance_provider');
            }
            if (!Schema::hasColumn('patients', 'reception_note')) {
                $table->text('reception_note')->nullable()->after('reception_flags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['insurance_status', 'insurance_provider', 'reception_flags', 'reception_note']);
        });
    }
};