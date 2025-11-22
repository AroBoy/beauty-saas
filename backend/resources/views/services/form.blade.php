<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $service->exists ? 'Edycja usługi' : 'Nowa usługa' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $service->exists ? 'Edycja usługi' : 'Nowa usługa' }}
                </h1>
                <p class="text-sm text-gray-500">Zdefiniuj parametry usługi.</p>
            </div>

            <x-status />

            <form method="POST" action="{{ $service->exists ? route('services.update', $service) : route('services.store') }}" class="space-y-6">
                @csrf
                @if($service->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nazwa</label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Czas trwania (min)</label>
                    <input type="number" name="duration_min" value="{{ old('duration_min', $service->duration_min) }}" required min="1"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('duration_min')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cena</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $service->price) }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('price')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="active" value="1" {{ old('active', $service->active ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="text-sm text-gray-700">Aktywna</label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Zapisz
                    </button>
                    <a href="{{ route('services.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Powrót</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
