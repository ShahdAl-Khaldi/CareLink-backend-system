<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * عمود users.status انعمل أصلاً بـ default(false) بأول migration للجدول.
     * أغلب أماكن إنشاء المستخدم (تسجيل مريض، تسجيل طبيب، إضافة طبيب من الأدمن)
     * ما كانت تبعت status صراحةً، فكانت بتعتمد على هاد الـ default - يعني أي
     * حساب جديد كان بيصير status=false (موقوف) بشكل افتراضي على أي قاعدة بيانات
     * جديدة (نشر جديد، أو بيئة اختبار). بعد ما CheckRole صار يرفض أي طلب لمستخدم
     * status=false، هاد كان رح يمنع كل مستخدم جديد من استخدام أي صفحة محمية
     * فوراً بعد التسجيل، من غير ما حدا يوقفه فعلياً. صلّحنا كل أماكن الإنشاء
     * لتبعت status=true صراحةً، وهون كمان منصلح الـ default نفسه دفاعياً.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('status')->default(false)->change();
        });
    }
};
