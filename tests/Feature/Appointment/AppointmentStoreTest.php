<?php

use App\Models\Appointment;
use App\Models\User;

it('يقدر المريض يحجز موعد مع طبيب', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1000000001',
    ]);

    $doctor = User::factory()->doctor()->create();

    $response = $this->actingAs($patient, 'sanctum')->postJson('/api/patient/appointments', [
        'doctor_id' => $doctor->id,
        'scheduled_at' => '2026-08-15 10:00:00',
        'type' => 'in_person',
        'description' => 'فحص دوري',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('message', 'تم حجز الموعد بنجاح');
    $response->assertJsonPath('data.doctor_id', $doctor->id);
    $response->assertJsonPath('data.patient_id', $patient->id);
    $response->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'status' => 'pending',
    ]);
});

it('يرفض حجز موعد محجوز مسبقاً', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1000000002',
    ]);

    $otherPatient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1000000003',
    ]);

    $doctor = User::factory()->doctor()->create();

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $otherPatient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-15 11:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->postJson('/api/patient/appointments', [
        'doctor_id' => $doctor->id,
        'scheduled_at' => '2026-08-15 11:00:00',
    ]);

    $response->assertStatus(409);
    $response->assertJson(['message' => 'هذا الموعد محجوز مسبقاً']);
});

it('يرفض حجز موعد بدون تسجيل دخول', function () {
    $doctor = User::factory()->doctor()->create();

    $response = $this->postJson('/api/patient/appointments', [
        'doctor_id' => $doctor->id,
        'scheduled_at' => '2026-08-15 12:00:00',
    ]);

    $response->assertStatus(401);
});

it('يرفض حجز موعد مع طبيب غير موجود', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1000000004',
    ]);

    $nonDoctor = User::factory()->create([
        'role' => 'patient',
        'national_id' => '1000000005',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->postJson('/api/patient/appointments', [
        'doctor_id' => $nonDoctor->id,
        'scheduled_at' => '2026-08-15 13:00:00',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['doctor_id']);
});
