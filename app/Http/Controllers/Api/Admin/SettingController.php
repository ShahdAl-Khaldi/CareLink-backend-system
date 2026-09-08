<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
 class SettingController extends Controller {
    public function index() {
        // نرجع الإعدادات كـ Object واحد سهل للقراءة
        return response()->json(Setting::pluck('value', 'key'));
    }

    public function update(Request $request) {
        // حفظ أو تحديث كل الإعدادات المرسلة - value عمود text بدون أي cast،
        // فبوليان PHP (true/false) كان يتخزن كـ "1"/"" بدل النص "true"/"false"
        // يلي الفرونت (SiteSettingsPage) بيقارن الـ checkbox عليه، فبعد أي حفظ
        // وإعادة تحميل كانت كل الـ checkboxes ترجع تظهر غير مفعّلة حتى لو
        // فعلياً محفوظة "مفعّلة" بقاعدة البيانات
        foreach ($request->all() as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return response()->json(['message' => 'تم الحفظ']);
    }
}