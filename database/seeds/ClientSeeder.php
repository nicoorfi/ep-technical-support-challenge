<?php

use App\User;
use App\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            throw new \RuntimeException('No users found. Make sure users are created before seeding clients.');
        }

        factory(Client::class, 150)->create()->each(function ($client) use ($users) {
            // Assign a random user as owner
            $client->user_id = $users->random()->id;
            $client->save();

            // Randomly assign 2-4 users who can access this client
            $assignedUsers = $users->random(rand(2, min(4, $users->count())));
            $client->assignedUsers()->sync($assignedUsers->pluck('id'));
        });
    }
}
