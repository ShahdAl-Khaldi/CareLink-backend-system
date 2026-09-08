<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'يرجى التأكد من صحة المدخلات المطلوبة.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // كان النموذج بيتحقق من البيانات بس ما بيحفظها ولا بيرسلها لحدا -
        // أي رسالة "تواصل معنا" كانت بتضيع نهائياً رغم رسالة النجاح
        ContactMessage::create($validator->validated());

        return response()->json([
            'message' => 'تم إرسال رسالتك بنجاح. سنقوم بالرد عليك في أقرب وقت.'
        ], 200);
    }

    // جلب معلومات التواصل ديناميكياً بناءً على ما حفظه الأدمن في SiteSettingsPage
    public function getContactSettings()
    {
        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'phone'        => $settings['supportPhone'] ?? '+970 59 123 4567',
            'email'        => $settings['supportEmail'] ?? 'info@carelink.com',
            'whatsapp'     => $settings['socialWhatsapp'] ?? 'https://wa.me/970591234567',
            'address'      => $settings['address'] ?? 'فلسطين، قطاع غزة',
            'workingHours' => $settings['workingHours'] ?? 'على مدار الساعة، 7 أيام',
        ], 200);
    }
}
