<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Usługi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Usługi</h1>
                    <p class="text-sm text-gray-500">Lista usług/typów wizyt.</p>
                </div>
                <a href="{{ route('services.create') }}"
                   class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Dodaj usługę
                </a>
            </div>

            <x-status />

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nazwa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Czas (min)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cena</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Aktywna</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($services as $service)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $service->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $service->duration_min }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $service->price }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $service->active ? 'tak' : 'nie' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('services.edit', $service) }}" class="text-indigo-600 hover:text-indigo-500">Edytuj</a>
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline-block ml-4"
                                      onsubmit="return confirm('Usunąć usługę?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Brak usług.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
