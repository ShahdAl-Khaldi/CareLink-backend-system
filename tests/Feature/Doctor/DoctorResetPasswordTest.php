<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

it('يقدر الطبيب يغير كلمة السر برمز التحقق الصحيح', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('oldpassword123'),
    ]);

    Cache::put('otp_doctor_'.$doctor->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/doctor/reset-password', [
        'email' => $doctor->email,
        'token' => 54321,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'تم تغيير كلمة السر بنجاح']);

    $doctor->refresh();
    expect(Hash::check('newpassword123', $doctor->password))->toBeTrue();
    expect(Cache::has('otp_doctor_'.$doctor->email))->toBeFalse();
});

it('يرفض تغيير كلمة السر برمز تحقق غلط', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('oldpassword123'),
    ]);

    Cache::put('otp_doctor_'.$doctor->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/doctor/reset-password', [
        'email' => $doctor->email,
        'token' => 99999,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400);
    $response->assertJson(['message' => 'الكود غير صحيح أو انتهت صلاحيته']);

    $doctor->refresh();
    expect(Hash::check('oldpassword123', $doctor->password))->toBeTrue();
});

it('يرفض تغيير كلمة السر بدون تأكيد كلمة السر', function () {
    $doctor = User::factory()->doctor()->create();

    Cache::put('otp_doctor_'.$doctor->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/doctor/reset-password', [
        'email' => $doctor->email,
        'token' => 54321,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});
