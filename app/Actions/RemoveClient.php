<?php

namespace App\Actions;

use App\Models\User;

class RemoveClient
{
    public function handle(User $client)
    {
        $trainer = auth()->user();
        // Detach the client from the trainer
        $trainer->clients()->detach($client->id);
    }
}
