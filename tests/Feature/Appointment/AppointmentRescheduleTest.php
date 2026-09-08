<?php

use App\Models\Appointment;
use App\Models\User;

it('يقدر المريض يعيد جدولة موعده', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '3000000001',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-17 10:00:00',
        'status' => 'pending',
        'type' => 'in_person',
        'description' => 'موعد قديم',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->patchJson("/api/patient/appointments/{$appointment->id}/reschedule", [
        'scheduled_at' => '2026-08-17 14:00:00',
        'description' => 'موعد جديد',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('message', 'تم تحديث الموعد بنجاح');
    $response->assertJsonPath('data.scheduled_at', '2026-08-17 14:00:00');
    $response->assertJsonPath('data.description', 'موعد جديد');
    $response->assertJsonPath('data.status', 'pending');
});

it('يرفض إعادة جدولة موعد لوقت محجوز مسبقاً', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '3000000002',
    ]);

    $otherPatient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '3000000003',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-18 10:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $otherPatient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-18 15:00:00',
        'status' => 'scheduled',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->patchJson("/api/patient/appointments/{$appointment->id}/reschedule", [
        'scheduled_at' => '2026-08-18 15:00:00',
    ]);

    $response->assertStatus(409);
    $response->assertJson(['message' => 'هذا الموعد محجوز مسبقاً']);
});

it('يرفض إعادة جدولة موعد مريض آخر', function () {
    $owner = User::factory()->create([
        'role' => 'patient',
        'national_id' => '3000000004',
    ]);

    $otherPatient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '3000000005',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $owner->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-19 10:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($otherPatient, 'sanctum')->patchJson("/api/patient/appointments/{$appointment->id}/reschedule", [
        'scheduled_at' => '2026-08-19 14:00:00',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'غير مصرح لك بتعديل هذا الموعد']);
});
