<?php

use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\User;

it('allows the doctor and patient linked to an appointment to access the conversation', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '9000000001',
    ]);

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-20 10:00:00',
        'status' => 'scheduled',
        'type' => 'in_person',
    ]);

    $conversation = Conversation::create([
        'appointment_id' => $appointment->id,
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
    ]);

    $doctorResponse = $this->actingAs($doctor, 'sanctum')->getJson("/api/appointments/{$appointment->id}/conversation");
    $doctorResponse->assertStatus(200);
    $doctorResponse->assertJsonPath('data.appointment_id', $appointment->id);

    $patientResponse = $this->actingAs($patient, 'sanctum')->getJson("/api/conversations/{$conversation->id}/messages");
    $patientResponse->assertStatus(200);
    $patientResponse->assertJsonStructure(['data', 'locked']);
});

it('rejects an unrelated doctor or patient from accessing the conversation and its messages', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '9000000002',
    ]);
    $unrelatedDoctor = User::factory()->doctor()->create();
    $unrelatedPatient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '9000000003',
    ]);

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-21 10:00:00',
        'status' => 'scheduled',
        'type' => 'in_person',
    ]);

    $conversation = Conversation::create([
        'appointment_id' => $appointment->id,
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
    ]);

    $this->actingAs($unrelatedDoctor, 'sanctum')
        ->getJson("/api/appointments/{$appointment->id}/conversation")
        ->assertStatus(403)
        ->assertJson(['message' => 'غير مصرح لك']);

    $this->actingAs($unrelatedDoctor, 'sanctum')
        ->getJson("/api/conversations/{$conversation->id}/messages")
        ->assertStatus(403)
        ->assertJson(['message' => 'غير مصرح لك']);

    $this->actingAs($unrelatedPatient, 'sanctum')
        ->getJson("/api/conversations/{$conversation->id}/messages")
        ->assertStatus(403)
        ->assertJson(['message' => 'غير مصرح لك']);
});

it('blocks sending messages after the appointment is completed', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '9000000004',
    ]);

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'patient_type' => User::class,
        'scheduled_at' => '2026-08-22 10:00:00',
        'status' => 'completed',
        'type' => 'in_person',
    ]);

    $conversation = Conversation::create([
        'appointment_id' => $appointment->id,
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
    ]);

    $response = $this->actingAs($patient, 'sanctum')->postJson("/api/conversations/{$conversation->id}/messages", [
        'body' => 'This message should be blocked',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'الموعد منتهي، ما بتقدر تبعت رسائل']);
    $this->assertDatabaseCount('messages', 0);
});
