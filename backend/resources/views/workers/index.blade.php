<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pracownicy
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Pracownicy</h1>
                    <p class="text-sm text-gray-500">Lista stanowisk/pracowników w salonie.</p>
                </div>
                <a href="{{ route('workers.create') }}"
                   class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Dodaj pracownika
                </a>
            </div>

            <x-status />

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nazwa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Aktywny</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Kolor</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($workers as $worker)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $worker->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $worker->active ? 'tak' : 'nie' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($worker->color_hex)
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-4 w-4 rounded" style="background: {{ $worker->color_hex }}"></span>
                                        <span class="text-gray-700">{{ $worker->color_hex }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('workers.edit', $worker) }}" class="text-indigo-600 hover:text-indigo-500">Edytuj</a>
                                <form action="{{ route('workers.destroy', $worker) }}" method="POST" class="inline-block ml-4"
                                      onsubmit="return confirm('Usunąć pracownika?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Brak pracowników.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
