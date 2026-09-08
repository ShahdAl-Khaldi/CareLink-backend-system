@component('mail::message')
# أهلاً دكتور {{ $data['name'] }}

تهانينا! تمنك الآن البدء باستخدام النظام واستقبال المرضى.
 تفعيل حسابك في منصة **CareLink** بنجاح.
يمك
@component('mail::button', ['url' => 'https://carelink.com/login'])
تسجيل الدخول
@endcomponent

شكراً لك,<br>
فريق {{ config('app.name') }}
@endcomponent