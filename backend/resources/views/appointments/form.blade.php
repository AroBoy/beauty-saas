<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $appointment->exists ? 'Edycja wizyty' : 'Nowa wizyta' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $appointment->exists ? 'Edycja wizyty' : 'Nowa wizyta' }}
                </h1>
                <p class="text-sm text-gray-500">Ustaw pracownika, klienta, usługę i czas.</p>
            </div>

            <x-status />

            <form method="POST" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}" class="space-y-6">
                @csrf
                @if($appointment->exists)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pracownik</label>
                        <select name="worker_id" required
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- wybierz --</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" @selected(old('worker_id', $appointment->worker_id) == $worker->id)>{{ $worker->name }}</option>
                            @endforeach
                        </select>
                        @error('worker_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Klient</label>
                        <select name="client_id" required
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- wybierz --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id', $appointment->client_id) == $client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Usługa</label>
                        <select name="service_id" required
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- wybierz --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id) == $service->id)>
                                    {{ $service->name }} ({{ $service->duration_min }}m)
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data i godzina rozpoczęcia</label>
                        <input type="datetime-local" name="starts_at"
                               value="{{ old('starts_at', optional($appointment->starts_at)->format('Y-m-d\\TH:i')) }}"
                               required
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('starts_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Czas trwania (min)</label>
                        <input type="number" name="duration_min" min="1"
                               value="{{ old('duration_min', $appointment->duration_min ?? 30) }}" required
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('duration_min')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @php($statuses = ['planned' => 'Planowana', 'confirmed' => 'Potwierdzona', 'cancelled' => 'Anulowana', 'no_show' => 'No show', 'completed' => 'Zakończona'])
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $appointment->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cena</label>
                        <input type="number" step="0.01" name="price_charged" value="{{ old('price_charged', $appointment->price_charged) }}"
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('price_charged')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notatki</label>
                    <textarea name="notes" rows="3"
                              class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $appointment->notes) }}</textarea>
                    @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Zapisz
                    </button>
                    <a href="{{ route('appointments.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Powrót</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
