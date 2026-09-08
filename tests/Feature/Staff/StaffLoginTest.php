<?php

use App\Models\User;

it('يقدر موظف الاستقبال يسجل دخول ببيانات صحيحة', function () {
    $staff = User::factory()->create([
        'role' => 'reception',
        'status' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/staff/login', [
        'email' => $staff->email,
        'password' => 'password123',
        'role' => 'reception',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['message', 'token', 'user']);
    $response->assertJsonPath('message', 'تم تسجيل الدخول بنجاح');
    $response->assertJsonPath('user.email', $staff->email);
    $response->assertJsonPath('user.role', 'reception');
});

it('يرفض تسجيل الدخول بباسورد غلط', function () {
    $staff = User::factory()->create([
        'role' => 'reception',
        'status' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/staff/login', [
        'email' => $staff->email,
        'password' => 'wrong-password',
        'role' => 'reception',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('يرفض تسجيل الدخول لدور مختلف عن دور المستخدم', function () {
    $staff = User::factory()->create([
        'role' => 'reception',
        'status' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/staff/login', [
        'email' => $staff->email,
        'password' => 'password123',
        'role' => 'pharmacy',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('يرفض تسجيل الدخول لحساب موقوف', function () {
    $staff = User::factory()->create([
        'role' => 'reception',
        'status' => false,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/staff/login', [
        'email' => $staff->email,
        'password' => 'password123',
        'role' => 'reception',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'هذا الحساب موقوف من قبل الإدارة.']);
});

it('يرفض تسجيل الدخول بدون تحديد الدور', function () {
    $staff = User::factory()->create([
        'role' => 'reception',
        'status' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/staff/login', [
        'email' => $staff->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['role']);
});
