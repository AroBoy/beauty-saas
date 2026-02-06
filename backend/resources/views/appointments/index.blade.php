<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kalendarz (dzień)
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Kalendarz <span id="calendar-day-label" class="text-gray-500">({{ $date }})</span></h1>
                    <p class="text-sm text-gray-500">Przeciągaj wizyty między pracownikami i godzinami.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="prev-day" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">←</button>
                    <button type="button" id="today" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">Dziś</button>
                    <button type="button" id="next-day" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">→</button>
                    <input type="text" id="date-picker" value="{{ $date }}" class="rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-32">
                    <button type="button" id="qa-open" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Dodaj wizytę
                    </button>
                </div>
            </div>

            <x-status />

            <div
                id="calendar"
                data-date="{{ $date }}"
                data-api-base="{{ config('app.url') }}"
                data-feed="{{ route('appointments.feed') }}"
                data-resources="{{ route('appointments.resources') }}"
                data-clients-search="{{ route('clients.search') }}"
                data-move="{{ route('appointments.index') }}"
                class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
            ></div>
        </div>
    </div>

    <div id="quick-appointment-backdrop" class="fixed inset-0 z-30 hidden bg-black/30"></div>
    <div id="quick-appointment-modal" class="fixed inset-0 z-40 hidden items-center justify-center">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Nowa wizyta</h3>
                    <p class="text-sm text-gray-500">Ustaw pracownika, klienta i godzinę.</p>
                </div>
                <button type="button" id="qa-close" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="quick-appointment-form" class="space-y-4">
                @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pracownik</label>
                            <select name="worker_id" id="qa-worker" class="mt-1 block w-full rounded border-gray-300 text-sm">
                                @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700">Klient</label>
                        <input type="hidden" name="client_id" id="qa-client-id" value="{{ $clients->first()->id ?? '' }}">
                        <input type="text" id="qa-client-search" class="mt-1 block w-full rounded border-gray-300 text-sm"
                               placeholder="Wpisz imię lub telefon" autocomplete="off"
                               value="{{ $clients->first()->name ?? '' }}">
                        <ul id="qa-client-suggestions"
                            class="absolute z-50 mt-1 hidden w-full rounded border border-gray-200 bg-white shadow">
                        </ul>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Usługa</label>
                        <select name="service_id" id="qa-service" class="mt-1 block w-full rounded border-gray-300 text-sm">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" data-duration="{{ $service->duration_min }}">{{ $service->name }} ({{ $service->duration_min }}m)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start</label>
                        <input type="text" id="qa-start" name="starts_at" class="mt-1 block w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Czas (min)</label>
                        <input type="number" min="1" id="qa-duration" name="duration_min" value="30" class="mt-1 block w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cena</label>
                        <input type="number" step="0.01" id="qa-price" name="price_charged" class="mt-1 block w-full rounded border-gray-300 text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Zapisz</button>
                    <button type="button" id="qa-cancel" class="text-sm text-gray-600 hover:text-gray-800">Anuluj</button>
                </div>
            </form>
        </div>
    </div>

    @include('appointments.edit_modal')

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/fullcalendar@6.1.15/index.global.min.css">
        <link rel="stylesheet" href="https://unpkg.com/@fullcalendar/resource-timegrid@6.1.15/index.global.min.css">
    @endpush

    {{-- FullCalendar inicjalizowany w resources/js/app.js --}}
</x-app-layout>
