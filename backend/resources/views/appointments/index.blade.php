<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kalendarz (dzień)
        </h2>
    </x-slot>

    <div class="py-6 bg-[radial-gradient(circle_at_top_left,#ffe8ee_0%,transparent_24%),radial-gradient(circle_at_top_right,#e6f5ef_0%,transparent_22%),linear-gradient(180deg,#fffaf8_0%,#fffdfb_42%,#f6fbfa_100%)]">
        <div class="mx-auto px-4 sm:px-6 lg:px-10">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-[2rem] border border-[#efd7dc] bg-[linear-gradient(135deg,#fff0f3_0%,#fff8f3_45%,#eef8f5_100%)] px-6 py-6 shadow-[0_18px_50px_rgba(214,176,187,0.18)]">
                <div>
                    <p class="mb-2 inline-flex rounded-full border border-white/70 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#a06f77] shadow-sm">
                        Kalendarz dnia
                    </p>
                    <h1 class="text-3xl font-semibold tracking-tight text-[#342824]">
                        Kalendarz <span id="calendar-day-label" class="text-[#7b6966]">{{ \Carbon\Carbon::parse($date)->locale('pl')->translatedFormat('d.m.Y, l') }}</span>
                    </h1>
                    <p class="mt-2 text-sm text-[#786664]">Przeciągaj wizyty między pracownikami i godzinami.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="prev-day" class="rounded-full border border-[#e6cac3] bg-white/80 px-3 py-2 text-sm text-[#6f5b57] shadow-sm transition hover:bg-white">←</button>
                    <button type="button" id="today" class="rounded-full border border-[#e6cac3] bg-white/80 px-4 py-2 text-sm font-medium text-[#6f5b57] shadow-sm transition hover:bg-white">Dziś</button>
                    <button type="button" id="next-day" class="rounded-full border border-[#e6cac3] bg-white/80 px-3 py-2 text-sm text-[#6f5b57] shadow-sm transition hover:bg-white">→</button>
                    <input type="text" id="date-picker" value="{{ $date }}" class="w-32 rounded-full border-[#e6cac3] bg-white/85 text-sm text-[#574844] shadow-sm focus:border-[#d9ada3] focus:ring-[#e6c3bb]">
                    <button type="button" id="qa-open" class="rounded-full bg-[linear-gradient(135deg,#e8b4ba_0%,#efc5b8_45%,#e8d4bc_100%)] px-5 py-2 text-sm font-semibold text-[#2f2522] shadow-[0_12px_30px_rgba(220,182,180,0.24)] transition hover:brightness-[0.99]">
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
                data-slot-min-time="{{ $slotMinTime }}"
                data-slot-max-time="{{ $slotMaxTime }}"
                data-weekday-slot-min-time="{{ $weekdaySlotMinTime }}"
                data-weekday-slot-max-time="{{ $weekdaySlotMaxTime }}"
                data-saturday-slot-min-time="{{ $saturdaySlotMinTime }}"
                data-saturday-slot-max-time="{{ $saturdaySlotMaxTime }}"
                data-sunday-slot-min-time="{{ $sundaySlotMinTime }}"
                data-sunday-slot-max-time="{{ $sundaySlotMaxTime }}"
                data-saturday-closed="{{ $saturdayClosed ? '1' : '0' }}"
                data-sunday-closed="{{ $sundayClosed ? '1' : '0' }}"
                data-closed-message="Salon jest zamknięty tego dnia."
                class="calendar-shell overflow-hidden rounded-[2rem] border border-[#efdcd7] bg-white/90 shadow-[0_20px_55px_rgba(219,193,186,0.14)] backdrop-blur"
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
