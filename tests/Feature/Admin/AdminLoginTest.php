<?php

use App\Models\User;

it('يقدر الأدمن يسجل دخول ببيانات صحيحة', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/admin/login', [
        'email' => $admin->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'token_type', 'user']);
});

it('يرفض تسجيل الدخول بباسورد غلط', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/admin/login', [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

it('يرفض تسجيل الدخول لمستخدم مش أدمن حتى لو الباسورد صح', function () {
    $doctor = User::factory()->create([
        'role' => 'doctor',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/admin/login', [
        'email' => $doctor->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
});