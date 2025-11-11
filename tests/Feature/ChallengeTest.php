<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_only_sees_clients_they_own_or_are_assigned_to(): void
    {
        $owner = $this->newUser();
        $client = $owner->createClient();
        $clientToAssign = $owner->createClient();

        $assignedUser = $this->newUser();
        $assignedUser->assignClient($clientToAssign);

        $unrelatedUser = $this->newUser();
        $unrelatedClient = $unrelatedUser->createClient();

        $response = $this->actingAs($assignedUser->user)->getJson('/clients');

        $response->assertStatus(200);

        $clientIds = collect($response->json())->pluck('id');

        $this->assertTrue($clientIds->contains($clientToAssign->client->id), 'Assigned user should see the client they are assigned to');
        $this->assertFalse($clientIds->contains($client->client->id), 'Assigned user should not see the client they own');
        $this->assertFalse($clientIds->contains($unrelatedClient->client->id), 'Assigned user should not see the unrelated client');
        $this->assertCount(1, $clientIds, 'Assigned user should only see the client they are assigned to');
    }

    /** @test */
    public function creating_client_requires_authenticated_user_and_sets_user_id(): void
    {
        $user = $this->newUser();

        $this->actingAs($user->user);

        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'city' => 'New York',
            'postcode' => '10001',
        ];

        $response = $this->post('/clients', $clientData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_id' => $user->user->id,
        ]);

        $this->post('/logout');

        $response = $this->post('/clients', $clientData);

        $response->assertRedirect('/login');
    }


    /** @test */
    public function client_index_view_includes_clients_with_bookings(): void
    {
        $user = $this->newUser();
        $client = $user->createClient(['name' => 'Test Client']);
        $client->addBookings(3);

        $this->assertArrayHasKey('bookings', $client->client->toArray(), 'Client toArray should have bookings key');

        $response = $this->actingAs($user->user)->get('/clients');

        $response->assertStatus(200);
        $response->assertViewIs('clients.index');
        $response->assertViewHas('clients');

        $clients = $response->viewData('clients');

        $this->assertCount(1, $clients, 'User should see one client in view');

        $clientModel = $clients->first();

        $this->assertEquals('Test Client', $clientModel->name);
        $this->assertCount(3, $clientModel->bookings, 'Client should have 3 bookings available');
    }
}
