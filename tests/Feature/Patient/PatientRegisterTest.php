<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('يقدر المريض يسجل حساب جديد ببيانات صحيحة', function () {
    $response = $this->postJson('/api/auth/patient/register', [
        'name' => 'مريض تجريبي',
        'email' => 'patient@example.com',
        'password' => 'password123',
        'phone' => '0500000000',
        'national_id' => '1234567890',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['message', 'access_token', 'user']);
    $response->assertJsonPath('user.role', 'patient');
    $response->assertJsonPath('user.email', 'patient@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'patient@example.com',
        'role' => 'patient',
        'national_id' => '1234567890',
    ]);

    $user = User::where('email', 'patient@example.com')->first();
    expect($user->patientProfile)->not->toBeNull();
});

it('يرفض التسجيل بإيميل مستخدم مسبقاً', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
        'national_id' => '1111111111',
    ]);

    $response = $this->postJson('/api/auth/patient/register', [
        'name' => 'مريض جديد',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'phone' => '0500000001',
        'national_id' => '2222222222',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('يرفض التسجيل برقم هوية مستخدم مسبقاً', function () {
    User::factory()->create([
        'email' => 'other@example.com',
        'national_id' => '3333333333',
    ]);

    $response = $this->postJson('/api/auth/patient/register', [
        'name' => 'مريض جديد',
        'email' => 'new@example.com',
        'password' => 'password123',
        'phone' => '0500000002',
        'national_id' => '3333333333',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['national_id']);
});

it('يرفض التسجيل بدون حقول مطلوبة', function () {
    $response = $this->postJson('/api/auth/patient/register', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password', 'phone', 'national_id']);
});
