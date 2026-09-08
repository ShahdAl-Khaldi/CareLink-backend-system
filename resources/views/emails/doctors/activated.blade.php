@component('mail::message')
# أهلاً من جديد دكتور {{ $data['name'] }}

تم تفعيل حسابك مجدداً على منصة **CareLink**، وبإمكانك الآن تسجيل الدخول ومتابعة استقبال المرضى.

@component('mail::button', ['url' => 'https://carelink.com/login'])
تسجيل الدخول
@endcomponent

شكراً لك,<br>
فريق {{ config('app.name') }}
@endcomponent
