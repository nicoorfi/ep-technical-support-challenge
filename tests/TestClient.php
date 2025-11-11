<?php

declare(strict_types=1);

namespace Tests;

use App\Booking;
use App\Client;

class TestClient
{
    public Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addBookings(int $count): self
    {
        factory(Booking::class, $count)->create(['client_id' => $this->client->id]);

        return $this;
    }
}
