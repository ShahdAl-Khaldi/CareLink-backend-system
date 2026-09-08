<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// مش صفحة حقيقية - بس اسمها "login" لازم يكون موجود. لما أي طلب API يوصل بدون
// تسجيل دخول (وما كان طالب JSON صراحة بالـ Accept header)، Laravel بيحاول
// يبني رابط لصفحة login قبل ما يرمي خطأ "غير مصرح" - ولو الراوت مش موجود
// بينكسر بخطأ تاني (RouteNotFoundException) وبيطلع صفحة تفاصيل تقنية كاملة
// للمستخدم بدل رسالة "غير مصرح" النظيفة. وجود الراوت هون بس بيمنع هالانكسار.
Route::get('/login', function () {
    return response()->json(['message' => 'غير مصرح لك بالدخول'], 401);
})->name('login');

// Route::get('/temp-test', function () {
//     return [
//         'temp' => sys_get_temp_dir(),
//         'exists' => file_exists(sys_get_temp_dir()),
//         'is_dir' => is_dir(sys_get_temp_dir()),
//         'writable' => is_writable(sys_get_temp_dir()),
//     ];
// });
