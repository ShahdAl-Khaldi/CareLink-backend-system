<?php

use App\Mail\GeneralNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

it('يرسل رمز التحقق للمريض عند طلب نسيان كلمة السر', function () {
    Mail::fake();

    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '7777777777',
    ]);

    $response = $this->postJson('/api/auth/patient/forgot-password', [
        'email' => $patient->email,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني']);

    Mail::assertSent(GeneralNotificationMail::class, function (GeneralNotificationMail $mail) use ($patient) {
        return $mail->hasTo($patient->email)
            && $mail->mailSubject === 'إعادة تعيين كلمة السر'
            && $mail->template === 'emails.auth.password_reset'
            && isset($mail->data['otp']);
    });

    expect(Cache::has('otp_patient_'.$patient->email))->toBeTrue();
});

it('يرفض طلب نسيان كلمة السر لإيميل غير موجود', function () {
    Mail::fake();

    $response = $this->postJson('/api/auth/patient/forgot-password', [
        'email' => 'missing@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);

    Mail::assertNothingSent();
});
