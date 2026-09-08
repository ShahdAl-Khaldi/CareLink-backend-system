<?php

use App\Models\Appointment;
use App\Models\User;

it('يقدر المريض يلغي موعده', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '2000000001',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-16 10:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->deleteJson("/api/patient/appointments/{$appointment->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('message', 'تم إلغاء الموعد بنجاح');
    $response->assertJsonPath('appointment.status', 'cancelled');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'cancelled',
    ]);
});

it('يرفض إلغاء موعد مريض آخر', function () {
    $owner = User::factory()->create([
        'role' => 'patient',
        'national_id' => '2000000002',
    ]);

    $otherPatient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '2000000003',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $owner->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-16 11:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($otherPatient, 'sanctum')->deleteJson("/api/patient/appointments/{$appointment->id}");

    $response->assertStatus(403);
    $response->assertJson(['message' => 'غير مصرح لك بإلغاء هذا الموعد']);

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => 'pending',
    ]);
});

it('يقدر الطبيب يلغي موعده', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '2000000004',
    ]);

    $doctor = User::factory()->doctor()->create();

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-16 12:00:00',
        'status' => 'scheduled',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($doctor, 'sanctum')->deleteJson("/api/doctor/appointments/{$appointment->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('appointment.status', 'cancelled');
});
