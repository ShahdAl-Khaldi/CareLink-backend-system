<?php

use App\Models\User;
use App\Models\Inventory;
it('returns 403 for a patient on admin routes', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '7000000001',
    ]);

    foreach (['/api/admin/doctors', '/api/admin/appointments'] as $route) {
        $response = $this->actingAs($patient, 'sanctum')->getJson($route);

        $response->assertStatus(403);
    }
});

 
it('returns 403 for a patient on inventory write routes', function () {
    $patient = User::factory()->create([
        'role' => 'patient',
        'national_id' => '7000000002',
    ]);

    $inventoryItem = Inventory::factory()->create();

    foreach ([
        ['/api/inventory/items', 'post'],
        ["/api/inventory/items/{$inventoryItem->id}", 'put'],
        ["/api/inventory/items/{$inventoryItem->id}/adjust", 'post'],
    ] as [$route, $method]) {
        $response = match ($method) {
            'post' => $this->actingAs($patient, 'sanctum')->postJson($route, []),
            'put' => $this->actingAs($patient, 'sanctum')->putJson($route, []),
            default => $this->actingAs($patient, 'sanctum')->postJson($route, []),
        };

        $response->assertStatus(403);
    }
});

it('returns 403 for a doctor on admin routes', function () {
    $doctor = User::factory()->doctor()->create();

    foreach (['/api/admin/doctors', '/api/admin/appointments'] as $route) {
        $response = $this->actingAs($doctor, 'sanctum')->getJson($route);

        $response->assertStatus(403);
    }
});
