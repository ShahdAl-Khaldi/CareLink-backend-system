<?php

use App\Models\Appointment;
use App\Models\User;

it('يرجع الأوقات المحجوزة للطبيب في يوم محدد', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '4000000001',
    ]);

    $doctor = User::factory()->doctor()->create();

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-20 09:30:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-20 14:00:00',
        'status' => 'scheduled',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->getJson("/api/patient/doctors/{$doctor->id}/booked-slots?date=2026-08-20");

    $response->assertStatus(200);
    $response->assertJsonPath('data', ['09:30', '14:00']);
});

it('ما بيرجع المواعيد الملغاة ضمن الأوقات المحجوزة', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '4000000002',
    ]);

    $doctor = User::factory()->doctor()->create();

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-21 10:00:00',
        'status' => 'cancelled',
        'type' => 'in_person',
    ]);

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-21 11:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->getJson("/api/patient/doctors/{$doctor->id}/booked-slots?date=2026-08-21");

    $response->assertStatus(200);
    $response->assertJsonPath('data', ['11:00']);
});

it('يرجع قائمة فارغة إذا ما انبعت تاريخ', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '4000000003',
    ]);

    $doctor = User::factory()->doctor()->create();

    Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-22 10:00:00',
        'status' => 'pending',
        'type' => 'in_person',
    ]);

    $response = $this->actingAs($patient, 'sanctum')->getJson("/api/patient/doctors/{$doctor->id}/booked-slots");

    $response->assertStatus(200);
    $response->assertJsonPath('data', []);
});

it('يرفض جلب الأوقات المحجوزة بدون تسجيل دخول', function () {
    $doctor = User::factory()->doctor()->create();

    $response = $this->getJson("/api/patient/doctors/{$doctor->id}/booked-slots?date=2026-08-20");

    $response->assertStatus(401);
});
