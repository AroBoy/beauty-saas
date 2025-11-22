<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Klienci
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Klienci</h1>
                    <p class="text-sm text-gray-500">Baza klientów salonu.</p>
                </div>
                <a href="{{ route('clients.create') }}"
                   class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Dodaj klienta
                </a>
            </div>

            <x-status />

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Klient</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Telefon</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">E-mail</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($clients as $client)
                <tr class="client-row cursor-pointer"
                    data-name="{{ $client->name }}"
                    data-phone="{{ $client->phone }}"
                    data-email="{{ $client->email }}"
                    data-notes="{{ $client->notes }}"
                    data-avatar="{{ $client->avatar_url }}"
                    data-id="{{ $client->id }}"
                >
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div class="flex items-center gap-3">
                            @if($client->avatar_url)
                                <img src="{{ $client->avatar_url }}" alt="" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-600">
                                    {{ strtoupper(mb_substr($client->name, 0, 1)) }}
                                </div>
                            @endif
                            <span>{{ $client->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $client->phone }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $client->email }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-500">Edytuj</a>
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline-block ml-4"
                              onsubmit="return confirm('Usunąć klienta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-500">Usuń</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Brak klientów.</td>
                </tr>
            @endforelse
            </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $clients->links() }}
            </div>
        </div>
    </div>

    <div id="client-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div id="client-modal-backdrop" class="fixed inset-0 bg-black/30"></div>
        <div class="relative w-full max-w-3xl max-h-[80vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
            <button type="button" id="client-modal-close" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">&times;</button>
            <div class="flex gap-6">
                <div class="flex-shrink-0">
                    <div id="client-avatar" class="h-24 w-24 rounded-full bg-gray-200"></div>
                </div>
                <div class="flex-1">
                    <div class="mb-4">
                        <h3 id="client-name" class="text-xl font-semibold text-gray-900"></h3>
                        <p id="client-email" class="text-sm text-gray-600"></p>
                    </div>
                    <dl class="space-y-2 text-sm text-gray-700">
                        <div class="flex">
                            <dt class="w-24 text-gray-500">Telefon</dt>
                            <dd id="client-phone"></dd>
                        </div>
                        <div class="flex">
                            <dt class="w-24 text-gray-500">E-mail</dt>
                            <dd id="client-email-detail"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Notatki</dt>
                            <dd id="client-notes" class="mt-1 whitespace-pre-line"></dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Ostatnie wizyty</h4>
                <ul id="client-history" class="divide-y divide-gray-200 rounded border border-gray-200 bg-gray-50"></ul>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const rows = document.querySelectorAll('.client-row');
                const modal = document.getElementById('client-modal');
                const backdrop = document.getElementById('client-modal-backdrop');
                const closeBtn = document.getElementById('client-modal-close');
                const avatar = document.getElementById('client-avatar');
                const nameEl = document.getElementById('client-name');
                const emailEl = document.getElementById('client-email');
                const emailDetail = document.getElementById('client-email-detail');
                const phoneEl = document.getElementById('client-phone');
                const notesEl = document.getElementById('client-notes');
                const historyEl = document.getElementById('client-history');
                const showBase = "{{ url('clients') }}";

                const openModal = (data) => {
                    if (data.avatar) {
                        avatar.innerHTML = `<img src="${data.avatar}" alt="" class="h-24 w-24 rounded-full object-cover">`;
                    } else {
                        avatar.innerHTML = `<div class="flex h-24 w-24 items-center justify-center rounded-full bg-gray-200 text-2xl font-semibold text-gray-600">${(data.name || '').charAt(0).toUpperCase()}</div>`;
                    }
                    nameEl.textContent = data.name || '';
                    emailEl.textContent = data.email || '';
                    emailDetail.textContent = data.email || '';
                    phoneEl.textContent = data.phone || '';
                    notesEl.textContent = data.notes || '';

                    historyEl.innerHTML = '';
                    if (data.appointments && data.appointments.length) {
                        data.appointments.forEach(item => {
                            const li = document.createElement('li');
                            li.className = 'px-3 py-2 text-sm text-gray-700';
                            const when = item.starts_at ? new Date(item.starts_at) : null;
                            const label = [
                                item.service || 'Wizyta',
                                item.worker ? `• ${item.worker}` : null,
                                when ? `• ${when.toLocaleDateString('pl-PL')} ${when.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })}` : null,
                                item.price ? `• ${item.price} zł` : null,
                            ].filter(Boolean).join(' ');
                            li.textContent = label;
                            historyEl.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.className = 'px-3 py-2 text-sm text-gray-500';
                        li.textContent = 'Brak wizyt';
                        historyEl.appendChild(li);
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                rows.forEach(row => {
                    row.addEventListener('click', async (e) => {
                        const dataset = e.currentTarget.dataset;
                        try {
                            const resp = await fetch(`${showBase}/${dataset.id}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!resp.ok) {
                                openModal({
                                    name: dataset.name || '',
                                    email: dataset.email || '',
                                    phone: dataset.phone || '',
                                    notes: dataset.notes || '',
                                    avatar: dataset.avatar || '',
                                    appointments: [],
                                });
                                return;
                            }
                            const data = await resp.json();
                            openModal(data);
                        } catch (err) {
                            openModal({
                                name: dataset.name || '',
                                email: dataset.email || '',
                                phone: dataset.phone || '',
                                notes: dataset.notes || '',
                                avatar: dataset.avatar || '',
                                appointments: [],
                            });
                        }
                    });
                });

                backdrop?.addEventListener('click', closeModal);
                closeBtn?.addEventListener('click', closeModal);
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeModal();
                });
            });
        </script>
    @endpush
</x-app-layout>
