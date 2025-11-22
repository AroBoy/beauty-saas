<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $client->exists ? 'Edycja klienta' : 'Nowy klient' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $client->exists ? 'Edycja klienta' : 'Nowy klient' }}
                </h1>
                <p class="text-sm text-gray-500">Uzupełnij dane klienta.</p>
            </div>

            <x-status />

    <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($client->exists)
            @method('PUT')
        @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Imię i nazwisko</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notatki</label>
            <textarea name="notes" rows="3"
                      class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $client->notes) }}</textarea>
            @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Zdjęcie profilowe</label>
            <input type="file" name="avatar" accept="image/*"
                   class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-indigo-500">
            @if($client->avatar_url)
                <div class="mt-2">
                    <img src="{{ $client->avatar_url }}" alt="Avatar" class="h-16 w-16 rounded-full object-cover">
                </div>
            @endif
            @error('avatar')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Zapisz
                    </button>
                    <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Powrót</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
