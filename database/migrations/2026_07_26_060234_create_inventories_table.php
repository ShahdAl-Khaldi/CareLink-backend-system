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
        // جدول inventories كان أصلاً موجود من قبل (name, quantity, min_quantity,
        // unit, keywords) - هون بس بنضيف الأعمدة الجديدة الناقصة بدل ما ننشئه
        // من الصفر، وإلا كان بيفشل بـ "table already exists" ويوقف كل الـ migrations يلي بعده
        Schema::table('inventories', function (Blueprint $table) {
            if (! Schema::hasColumn('inventories', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (! Schema::hasColumn('inventories', 'company')) {
                $table->string('company')->nullable()->after('category');
            }
            if (! Schema::hasColumn('inventories', 'price')) {
                // أقرب تاريخ صلاحية بين الدفعات (يُعاد حسابه تلقائياً)
                $table->decimal('price', 10, 2)->default(0)->after('unit');
            }
            if (! Schema::hasColumn('inventories', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('price');
            }
            if (! Schema::hasColumn('inventories', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('keywords');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // منشيل بس الأعمدة يلي هاي الـ migration ضافتهم، مش الجدول كامل
        // (الجدول أصلاً كان موجود قبلها)
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['category', 'company', 'price', 'expiry_date', 'updated_by']);
        });
    }
};
