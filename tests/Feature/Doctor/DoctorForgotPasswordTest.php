<?php

use App\Mail\GeneralNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

it('يرسل رمز التحقق للطبيب عند طلب نسيان كلمة السر', function () {
    Mail::fake();

    $doctor = User::factory()->doctor()->create();

    $doctor->doctorProfile()->create([
        'specialty' => 'أمراض القلب',
        'status' => 'active',
        'gender' => 'male',
        'years_of_experience' => 5,
    ]);

    $response = $this->postJson('/api/auth/doctor/forgot-password', [
        'email' => $doctor->email,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني']);

    Mail::assertSent(GeneralNotificationMail::class, function (GeneralNotificationMail $mail) use ($doctor) {
        return $mail->hasTo($doctor->email)
            && $mail->mailSubject === 'إعادة تعيين كلمة السر'
            && $mail->template === 'emails.auth.password_reset'
            && isset($mail->data['otp']);
    });

    expect(Cache::has('otp_doctor_'.$doctor->email))->toBeTrue();
});

it('يرفض طلب نسيان كلمة السر لإيميل غير موجود', function () {
    Mail::fake();

    $response = $this->postJson('/api/auth/doctor/forgot-password', [
        'email' => 'missing@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);

    Mail::assertNothingSent();
});
