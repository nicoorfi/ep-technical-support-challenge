<?php

declare(strict_types=1);

namespace Tests;

use App\Client;
use App\User;

class TestUser
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function createClient(array $attributes = []): TestClient
    {
        $client = factory(Client::class)->create([
            'user_id' => $this->user->id,
            ...$attributes,
        ]);

        return new TestClient($client);
    }

    public function assignClient(TestClient|Client $client): self
    {
        $clientModel = $client instanceof TestClient ? $client->client : $client;

        $clientModel->assignedUsers()->attach($this->user->id);

        return $this;
    }
}
