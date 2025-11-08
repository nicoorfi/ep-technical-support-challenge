<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Client::with(['user', 'assignedUsers']);

        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('email', 'ilike', '%' . $searchTerm . '%');
            });
        }

        $clients = $query->get();

        foreach ($clients as $client) {
            $client->append('bookings_count');
        }

        if ($request->wantsJson()) {
            return response()->json($clients);
        }

        return view('clients.index', ['clients' => $clients]);
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function show($client): View
    {
        $client = Client::where('id', $client)->first();

        $this->authorize('view', $client);

        return view('clients.show', ['client' => $client]);
    }

    public function store(Request $request): Client
    {
        $client = new Client;
        $client->name = $request->get('name');
        $client->email = $request->get('email');
        $client->phone = $request->get('phone');
        $client->address = $request->get('address');
        $client->city = $request->get('city');
        $client->postcode = $request->get('postcode');
        $client->user_id = auth()->id();
        $client->save();

        return $client;
    }

    public function destroy($client): string
    {
        $client = Client::where('id', $client)->first();

        $this->authorize('delete', $client);

        $client->delete();

        return 'Deleted';
    }
}
