<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

it('يقدر المريض يغير كلمة السر برمز التحقق الصحيح', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'password' => bcrypt('oldpassword123'),
        'national_id' => '8888888888',
    ]);

    Cache::put('otp_patient_'.$patient->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/patient/reset-password', [
        'email' => $patient->email,
        'token' => 54321,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'تم تغيير كلمة السر بنجاح']);

    $patient->refresh();
    expect(Hash::check('newpassword123', $patient->password))->toBeTrue();
    expect(Cache::has('otp_patient_'.$patient->email))->toBeFalse();
});

it('يرفض تغيير كلمة السر برمز تحقق غلط', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'password' => bcrypt('oldpassword123'),
        'national_id' => '9999999999',
    ]);

    Cache::put('otp_patient_'.$patient->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/patient/reset-password', [
        'email' => $patient->email,
        'token' => 99999,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400);
    $response->assertJson(['message' => 'الكود غير صحيح أو انتهت صلاحيته']);

    $patient->refresh();
    expect(Hash::check('oldpassword123', $patient->password))->toBeTrue();
});

it('يرفض تغيير كلمة السر بدون تأكيد كلمة السر', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1010101010',
    ]);

    Cache::put('otp_patient_'.$patient->email, 54321, now()->addMinutes(10));

    $response = $this->postJson('/api/auth/patient/reset-password', [
        'email' => $patient->email,
        'token' => 54321,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});
