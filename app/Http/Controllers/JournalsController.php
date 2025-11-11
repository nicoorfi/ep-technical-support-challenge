<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Client;
use App\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalsController extends Controller
{
    public function index($client): JsonResponse
    {
        $client = Client::where('id', $client)->first();

        $this->authorize('view', $client);

        $journals = $client->journals()->with('user')->latest()->get();

        return response()->json($journals);
    }

    public function store(Request $request, $client): Journal
    {
        $client = Client::where('id', $client)->first();

        $this->authorize('update', $client);

        $journal = new Journal;
        $journal->client_id = $client->id;
        $journal->user_id = auth()->id();
        $journal->content = $request->get('content');
        $journal->save();

        $journal->load('user');

        return $journal;
    }

    public function destroy($client, $journal): string
    {
        $journal = Journal::where('id', $journal)->first();

        $this->authorize('delete', $journal);

        $journal->delete();

        return 'Deleted';
    }
}
