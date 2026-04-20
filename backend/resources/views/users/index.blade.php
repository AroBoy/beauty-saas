<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Użytkownicy</h1>
                    <p class="text-sm text-gray-500">Konta administratorów i operatorów przypisanych do tego salonu.</p>
                </div>
                <a href="{{ route('users.create') }}"
                   class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Dodaj operatora
                </a>
            </div>

            @if (session('status') === 'user-created')
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Konto operatora zostało utworzone.
                </div>
            @endif

            @if (session('status') === 'user-updated')
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Konto operatora zostało zaktualizowane.
                </div>
            @endif

            @if (session('status') === 'user-deleted')
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Konto operatora zostało usunięte.
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Użytkownik</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">E-mail</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Rola</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Zweryfikowany</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $user->isOperator() ? 'operator' : 'admin' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $user->email_verified_at ? 'tak' : 'nie' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if($user->isOperator())
                                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-500">Edytuj</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="ml-4 inline-block"
                                          onsubmit="return confirm('Usunąć operatora?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Usuń</button>
                                    </form>
                                @else
                                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">konto główne</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Brak użytkowników w salonie.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
