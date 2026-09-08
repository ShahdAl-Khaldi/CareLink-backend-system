<?php

use App\Models\User;

it('يقدر المريض يسجل دخول ببيانات صحيحة', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'password' => bcrypt('password123'),
        'national_id' => '4444444444',
    ]);

    $response = $this->postJson('/api/auth/patient/login', [
        'email' => $patient->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'token_type', 'user']);
    $response->assertJsonPath('token_type', 'Bearer');
    $response->assertJsonPath('user.email', $patient->email);
});

it('يرفض تسجيل الدخول بباسورد غلط', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'password' => bcrypt('password123'),
        'national_id' => '5555555555',
    ]);

    $response = $this->postJson('/api/auth/patient/login', [
        'email' => $patient->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'بيانات الدخول غير صحيحة']);
});

it('يرفض تسجيل الدخول لمستخدم مش مريض حتى لو الباسورد صح', function () {
    $doctor = User::factory()->doctor()->create([
        'password' => bcrypt('password123'),
        'national_id' => '6666666666',
    ]);

    $response = $this->postJson('/api/auth/patient/login', [
        'email' => $doctor->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'بيانات الدخول غير صحيحة']);
});
