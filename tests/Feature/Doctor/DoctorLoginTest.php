<?php

use App\Models\User;

it('يقدر الطبيب المفعّل يسجل دخول ببيانات صحيحة', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('password123'),
    ]);

    $doctor->doctorProfile()->create([
        'specialty' => 'أمراض القلب',
        'status' => 'active',
        'gender' => 'male',
        'years_of_experience' => 5,
    ]);

    $response = $this->postJson('/api/auth/doctor/login', [
        'email' => $doctor->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'token_type', 'user']);
    $response->assertJsonPath('token_type', 'Bearer');
    $response->assertJsonPath('user.email', $doctor->email);
});

it('يرفض تسجيل الدخول بباسورد غلط', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('password123'),
    ]);

    $doctor->doctorProfile()->create([
        'specialty' => 'أمراض القلب',
        'status' => 'active',
        'gender' => 'male',
        'years_of_experience' => 5,
    ]);

    $response = $this->postJson('/api/auth/doctor/login', [
        'email' => $doctor->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'بيانات الدخول غير صحيحة']);
});

it('يرفض تسجيل الدخول لطبيب غير مفعّل', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('password123'),
    ]);

    $doctor->doctorProfile()->create([
        'specialty' => 'أمراض القلب',
        'status' => 'inactive',
        'gender' => 'male',
        'years_of_experience' => 5,
    ]);

    $response = $this->postJson('/api/auth/doctor/login', [
        'email' => $doctor->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'حسابك بانتظار موافقة الإدارة']);
});

it('يرفض تسجيل الدخول لمستخدم مش طبيب حتى لو الباسورد صح', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'password' => bcrypt('password123'),
        'national_id' => '1212121212',
    ]);

    $response = $this->postJson('/api/auth/doctor/login', [
        'email' => $patient->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'بيانات الدخول غير صحيحة']);
});
