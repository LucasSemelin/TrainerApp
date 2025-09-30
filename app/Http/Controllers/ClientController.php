<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Actions\CreateClient;
use App\Actions\RemoveClient;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        $trainer = Auth::user();

        $clients = $trainer->clients()->with('profile')->get();

        return inertia('PageClientsIndex', [
            'clients' => $clients
        ]);
    }

    public function store(Request $request, CreateClient $createClient)
    {
        $createClient->handle($request);

        return redirect()->route('clients.index')->with('success', 'Client created.');
    }

    public function destroy(Request $request, User $client, RemoveClient $removeClient)
    {
        $removeClient->handle($client);

        return redirect()->route('clients.index')->with('success', 'Client removed.');
    }
}
