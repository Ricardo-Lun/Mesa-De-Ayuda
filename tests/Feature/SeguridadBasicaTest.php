<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadBasicaTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_guest_cannot_list_services(): void
    {
        $this->getJson('/api/services')->assertUnauthorized();
    }

    public function test_authenticated_user_only_sees_their_filtered_paginated_services(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();

        $this->createService($carlos, 'pending');
        $this->createService($carlos, 'pending');
        $this->createService($carlos, 'completed');
        $this->createService($laura, 'pending');

        $response = $this->actingAs($carlos)
            ->getJson('/api/services?status=pending&per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $service) {
            $this->assertSame($carlos->id, $service['technician_id']);
            $this->assertSame('pending', $service['status']);
        }
    }

    public function test_client_cannot_choose_the_technician_when_creating_a_service(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();
        $device = $this->createDevice();

        $response = $this->actingAs($carlos)->postJson('/api/services', [
            'folio' => 'SEC-FORGED-STORE',
            'device_id' => $device->id,
            'technician_id' => $laura->id,
            'status' => 'pending',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('technician_id');

        $this->assertDatabaseMissing('services', [
            'folio' => 'SEC-FORGED-STORE',
        ]);
    }

    public function test_client_cannot_reassign_the_technician_when_updating_a_service(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();
        $service = $this->createService($carlos);

        $response = $this->actingAs($carlos)
            ->patchJson("/api/services/{$service->id}", [
                'technician_id' => $laura->id,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('technician_id');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'technician_id' => $carlos->id,
        ]);
    }

    public function test_user_cannot_view_another_technicians_service(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();
        $service = $this->createService($carlos);

        $this->actingAs($laura)
            ->getJson("/api/services/{$service->id}")
            ->assertForbidden();
    }

    public function test_user_cannot_update_another_technicians_service(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();
        $service = $this->createService($carlos);

        $this->actingAs($laura)
            ->patchJson("/api/services/{$service->id}", [
                'status' => 'completed',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_delete_another_technicians_service(): void
    {
        $carlos = User::factory()->create();
        $laura = User::factory()->create();
        $service = $this->createService($carlos);

        $this->actingAs($laura)
            ->deleteJson("/api/services/{$service->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
        ]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $carlos = User::factory()->create();
        $service = $this->createService($carlos);

        $this->actingAs($carlos)
            ->patchJson("/api/services/{$service->id}", [
                'status' => 'invented',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_nonexistent_service_returns_not_found(): void
    {
        $carlos = User::factory()->create();

        $this->actingAs($carlos)
            ->getJson('/api/services/999999')
            ->assertNotFound();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $inactive = User::factory()->create([
            'active' => false,
            'password' => 'password123',
        ]);

        $response = $this->postJson('/login', [
            'email' => $inactive->email,
            'password' => 'password123',
        ]);

        $this->assertContains($response->status(), [401, 422]);
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected_by_fortify(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnprocessable();

        $this->assertGuest();
    }

    public function test_authenticated_user_payload_never_exposes_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('remember_token');
    }

    private function createDevice(): Device
    {
        $this->sequence++;

        return Device::create([
            'owner' => "Owner {$this->sequence}",
            'area' => 'Testing',
            'brand' => 'Test Brand',
            'model' => "Model {$this->sequence}",
            'serial_number' => "TEST-SERIAL-{$this->sequence}",
            'type' => 'Laptop',
            'received_at' => '2026-10-18',
            'priority' => 'medium',
        ]);
    }

    private function createService(User $technician, string $status = 'pending'): Service
    {
        $device = $this->createDevice();

        return Service::create([
            'folio' => "TEST-SERVICE-{$this->sequence}",
            'device_id' => $device->id,
            'technician_id' => $technician->id,
            'status' => $status,
        ]);
    }
}
