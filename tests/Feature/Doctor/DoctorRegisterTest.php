<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// DoctorAuthController::register كان ينشئ doctorProfile بدون gender رغم إنه
// doctor_profiles.gender مطلوب NOT NULL بالـ migration - تم تصحيحه بجعل
// gender وyears_of_experience إلزاميين بالتحقق (نفس حقول فورم التسجيل الحقيقي)
it('يقدر الطبيب يسجل حساب جديد ببيانات صحيحة', function () {
    Storage::fake('public');

    $response = $this->post('/api/auth/doctor/register', [
        'name' => 'طبيب تجريبي',
        'email' => 'doctor@example.com',
        'password' => 'password123',
        'phone' => '0500000000',
        'specialty' => 'أمراض القلب',
        'gender' => 'male',
        'years_of_experience' => 5,
        'credential_document' => UploadedFile::fake()->create('credential.pdf', 100, 'application/pdf'),
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['message', 'access_token', 'user']);
    $response->assertJsonPath('user.role', 'doctor');
    $response->assertJsonPath('user.email', 'doctor@example.com');
    $response->assertJsonPath('user.doctor_profile.status', 'inactive');

    $this->assertDatabaseHas('users', [
        'email' => 'doctor@example.com',
        'role' => 'doctor',
    ]);

    $user = User::where('email', 'doctor@example.com')->first();
    expect($user->doctorProfile)->not->toBeNull();
    expect($user->doctorProfile->specialty)->toBe('أمراض القلب');
    expect($user->doctorProfile->status)->toBe('inactive');
    expect($user->doctorProfile->gender)->toBe('male');
    expect($user->doctorProfile->years_of_experience)->toBe(5);

    Storage::disk('public')->assertExists($user->doctorProfile->credential_document);
});

it('يرفض التسجيل بإيميل مستخدم مسبقاً', function () {
    Storage::fake('public');

    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/api/auth/doctor/register', [
        'name' => 'طبيب جديد',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'phone' => '0500000001',
        'specialty' => 'أمراض القلب',
        'credential_document' => UploadedFile::fake()->create('credential.pdf', 100, 'application/pdf'),
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('يرفض التسجيل بدون مستند الشهادة', function () {
    $response = $this->post('/api/auth/doctor/register', [
        'name' => 'طبيب جديد',
        'email' => 'newdoctor@example.com',
        'password' => 'password123',
        'phone' => '0500000002',
        'specialty' => 'أمراض القلب',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credential_document']);
});
