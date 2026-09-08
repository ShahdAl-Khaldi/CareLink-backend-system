@component('mail::message')
# إشعار جديد

{{ $data['message'] ?? 'لديك إشعار جديد من نظام CareLink.' }}

شكراً لك,<br>
فريق {{ config('app.name') }}
@endcomponent