<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $worker->exists ? 'Edycja pracownika' : 'Nowy pracownik' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $worker->exists ? 'Edycja pracownika' : 'Nowy pracownik' }}
                </h1>
                <p class="text-sm text-gray-500">Uzupełnij dane pracownika/stanowiska.</p>
            </div>

            <x-status />

            <form method="POST" action="{{ $worker->exists ? route('workers.update', $worker) : route('workers.store') }}" class="space-y-6">
                @csrf
                @if($worker->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Imię i nazwisko</label>
                    <input type="text" name="name" value="{{ old('name', $worker->name) }}" required
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="active" value="1" {{ old('active', $worker->active ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="text-sm text-gray-700">Aktywny</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kolor (HEX)</label>
                    <input type="text" name="color_hex" value="{{ old('color_hex', $worker->color_hex) }}" placeholder="#10b981"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('color_hex')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-2xl border border-[#ead9d5] bg-[#fffaf8] p-5">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tygodniowy grafik pracy</h3>
                        <p class="text-sm text-gray-500">Ustaw realne godziny pracy zamiast sztywnego zakresu 7:00-22:00.</p>
                    </div>

                    <div class="space-y-3">
                        @foreach($scheduleRows as $row)
                            @php($oldRow = old("schedule.{$row['day']}", $row))
                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-[#efe3df] bg-white px-4 py-3 md:grid-cols-[160px_110px_1fr_1fr] md:items-center">
                                <div class="font-medium text-gray-900">{{ $row['label'] }}</div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input
                                        type="hidden"
                                        name="schedule[{{ $row['day'] }}][is_working]"
                                        value="0"
                                    >
                                    <input
                                        type="checkbox"
                                        name="schedule[{{ $row['day'] }}][is_working]"
                                        value="1"
                                        {{ !empty($oldRow['is_working']) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    Pracuje
                                </label>
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Od</label>
                                    <input
                                        type="time"
                                        name="schedule[{{ $row['day'] }}][start_time]"
                                        value="{{ $oldRow['start_time'] ?? '' }}"
                                        class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Do</label>
                                    <input
                                        type="time"
                                        name="schedule[{{ $row['day'] }}][end_time]"
                                        value="{{ $oldRow['end_time'] ?? '' }}"
                                        class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('schedule.*.start_time')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                    @error('schedule.*.end_time')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-2xl border border-[#ead9d5] bg-[#fffaf8] p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Nieobecności i blokady czasu</h3>
                            <p class="text-sm text-gray-500">Urlop, szkolenie, wolne lub inna blokada, która ma wyciąć terminy z kalendarza.</p>
                        </div>
                        <button type="button" id="add-time-off-row" class="rounded-full border border-[#dfc7c0] px-4 py-2 text-sm font-medium text-gray-700 hover:bg-white">
                            Dodaj blokadę
                        </button>
                    </div>

                    <div id="time-off-rows" class="space-y-3">
                        @foreach(old('time_offs', $timeOffRows) as $index => $row)
                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-[#efe3df] bg-white px-4 py-3 lg:grid-cols-[1fr_1fr_160px_1fr_auto] lg:items-end">
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Od</label>
                                    <input type="datetime-local" name="time_offs[{{ $index }}][starts_at]" value="{{ $row['starts_at'] ?? '' }}" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Do</label>
                                    <input type="datetime-local" name="time_offs[{{ $index }}][ends_at]" value="{{ $row['ends_at'] ?? '' }}" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Typ</label>
                                    <select name="time_offs[{{ $index }}][type]" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @php($types = ['custom' => 'Blokada', 'vacation' => 'Urlop', 'sick' => 'Chorobowe', 'training' => 'Szkolenie', 'break' => 'Przerwa'])
                                        @foreach($types as $value => $label)
                                            <option value="{{ $value }}" @selected(($row['type'] ?? 'custom') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Notatka</label>
                                    <input type="text" name="time_offs[{{ $index }}][note]" value="{{ $row['note'] ?? '' }}" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <button type="button" class="remove-time-off-row rounded-full border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Usuń
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @error('time_offs.*.starts_at')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                    @error('time_offs.*.ends_at')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Zapisz
                    </button>
                    <a href="{{ route('workers.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Powrót</a>
                </div>
            </form>
        </div>
    </div>

    <template id="time-off-row-template">
        <div class="grid grid-cols-1 gap-3 rounded-xl border border-[#efe3df] bg-white px-4 py-3 lg:grid-cols-[1fr_1fr_160px_1fr_auto] lg:items-end">
            <div>
                <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Od</label>
                <input type="datetime-local" name="__NAME__[starts_at]" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Do</label>
                <input type="datetime-local" name="__NAME__[ends_at]" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Typ</label>
                <select name="__NAME__[type]" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="custom">Blokada</option>
                    <option value="vacation">Urlop</option>
                    <option value="sick">Chorobowe</option>
                    <option value="training">Szkolenie</option>
                    <option value="break">Przerwa</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs uppercase tracking-wide text-gray-500">Notatka</label>
                <input type="text" name="__NAME__[note]" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="button" class="remove-time-off-row rounded-full border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                Usuń
            </button>
        </div>
    </template>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('time-off-rows');
                const addButton = document.getElementById('add-time-off-row');
                const template = document.getElementById('time-off-row-template');

                if (!container || !addButton || !template) return;

                const bindRemoveButtons = () => {
                    container.querySelectorAll('.remove-time-off-row').forEach((button) => {
                        button.onclick = () => {
                            const rows = container.querySelectorAll('.remove-time-off-row');
                            if (rows.length === 1) {
                                button.closest('.grid')?.querySelectorAll('input').forEach((input) => input.value = '');
                                button.closest('.grid')?.querySelector('select')?.value = 'custom';
                                return;
                            }

                            button.closest('.grid')?.remove();
                        };
                    });
                };

                addButton.addEventListener('click', () => {
                    const index = container.querySelectorAll('.remove-time-off-row').length;
                    const html = template.innerHTML.replaceAll('__NAME__', `time_offs[${index}]`);
                    container.insertAdjacentHTML('beforeend', html);
                    bindRemoveButtons();
                });

                bindRemoveButtons();
            });
        </script>
    @endpush
</x-app-layout>
