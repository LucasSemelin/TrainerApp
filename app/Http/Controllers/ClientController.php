<?php

namespace App\Http\Controllers;

use App\Actions\CreateClient;
use App\Actions\RemoveClient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        $trainer = Auth::user();

        $clients = $trainer->clients()->with('profile')->get();

        return inertia('PageClientsIndex', [
            'clients' => $clients,
        ]);
    }

    public function show(User $client)
    {
        // $this->authorize('view', $client);

        $client->load('profile');

        return inertia('Clients/PageShow', [
            'client' => $client,
        ]);
    }

    public function store(Request $request, CreateClient $createClient)
    {
        try {
            $result = $createClient->handle($request);

            // If user exists and needs confirmation, return validation error
            if (is_array($result) && isset($result['user_exists'])) {
                return back()->withErrors([
                    'user_exists' => json_encode($result['user']),
                ]);
            }

            return redirect()->route('clients.index')->with('success', 'Cliente creado e invitación enviada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, User $client, RemoveClient $removeClient)
    {
        $removeClient->handle($client);

        return redirect()->route('clients.index')->with('success', 'Client removed.');
    }
}
