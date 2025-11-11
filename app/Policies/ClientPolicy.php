<?php

declare(strict_types=1);

namespace App\Policies;

use App\Client;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Client $client): bool
    {
        return $user->id === $client->user_id
            || $client->assignedUsers()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->id === $client->user_id
            || $client->assignedUsers()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }
}
