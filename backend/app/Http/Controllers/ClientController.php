<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::orderBy('name')->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client()]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        Client::create($data);

        return redirect()->route('clients.index')->with('status', 'Klient utworzony.');
    }

    public function edit(Client $client): View
    {
        return view('clients.form', compact('client'));
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            if ($client->avatar_path) {
                Storage::disk('public')->delete($client->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $client->update($data);

        return redirect()->route('clients.index')->with('status', 'Klient zaktualizowany.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->avatar_path) {
            Storage::disk('public')->delete($client->avatar_path);
        }
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Klient usunięty.');
    }

    public function show(Request $request, Client $client)
    {
        $client->load(['appointments' => function ($q) {
            $q->with(['service', 'worker'])
                ->orderByDesc('starts_at')
                ->limit(10);
        }]);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'phone' => $client->phone,
            'email' => $client->email,
            'notes' => $client->notes,
            'avatar' => $client->avatar_url,
            'appointments' => $client->appointments->map(fn ($a) => [
                'starts_at' => $a->starts_at?->toIso8601String(),
                'service' => $a->service?->name,
                'worker' => $a->worker?->name,
                'price' => $a->price_charged,
                'duration_min' => $a->duration_min,
            ]),
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->string('q')->trim();

        if ($q->isEmpty()) {
            return [];
        }

        $term = mb_strtolower($q->toString());

        $clients = Client::query()
            ->where(function ($query) use ($term) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$term}%"]);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email']);

        $payload = $clients->map(function (Client $client) {
            $label = $client->name;
            if ($client->phone) {
                $label .= ' • ' . $client->phone;
            } elseif ($client->email) {
                $label .= ' • ' . $client->email;
            }

            return [
                'id' => $client->id,
                'label' => $label,
            ];
        })->values();

        return response()->json($payload);
    }
}
