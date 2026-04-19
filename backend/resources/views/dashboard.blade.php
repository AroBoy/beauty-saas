<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="dashboard-page py-8 bg-[radial-gradient(circle_at_top_left,#ffe8ee_0%,transparent_26%),radial-gradient(circle_at_top_right,#e5f5ef_0%,transparent_24%),linear-gradient(180deg,#fffaf8_0%,#fffdfb_45%,#f6fbfa_100%)]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-hero relative mb-8 overflow-hidden rounded-[2rem] border border-[#efd7dc] bg-[linear-gradient(135deg,#fff0f3_0%,#fff9f4_42%,#eef8f5_100%)] p-8 text-slate-900 shadow-[0_24px_70px_rgba(214,176,187,0.22)]">
                <div class="pointer-events-none absolute -right-8 -top-8 h-36 w-36 rounded-full bg-white/35 blur-2xl"></div>
                <div class="pointer-events-none absolute bottom-0 left-1/3 h-28 w-28 rounded-full bg-[#f7d7dd]/50 blur-2xl"></div>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="mb-3 inline-flex rounded-full border border-white/70 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-[#a06f77] shadow-sm">Pulpit dnia</p>
                        <h1 class="text-4xl font-semibold tracking-tight text-[#342824]">Przegląd salonu na dziś</h1>
                        <p class="mt-3 max-w-xl text-base leading-7 text-[#6f5d5d]">
                            {{ \Illuminate\Support\Str::ucfirst($todayLabel) }}. Najważniejsze liczby, najbliższe wizyty i szybki dostęp do codziennej pracy.
                        </p>
                    </div>
                    <div class="dashboard-next relative rounded-[1.75rem] border border-white/70 bg-white/75 px-5 py-4 shadow-[0_16px_40px_rgba(181,144,151,0.16)] backdrop-blur">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.85),transparent)]"></div>
                        <p class="text-xs uppercase tracking-[0.22em] text-[#b18477]">Następna wizyta</p>
                        @if ($nextAppointment)
                            <p class="mt-2 text-2xl font-semibold text-[#342824]">{{ $nextAppointment->starts_at->format('H:i') }}</p>
                            <p class="mt-1 text-sm text-[#5e4d49]">
                                {{ $nextAppointment->client?->name ?? 'Klient' }} · {{ $nextAppointment->worker?->name ?? 'Pracownik' }}
                            </p>
                            <p class="mt-1 text-sm text-[#94766e]">{{ $nextAppointment->service?->name ?? 'Usługa' }}</p>
                        @else
                            <p class="mt-2 text-lg font-medium text-[#342824]">Brak nadchodzących wizyt</p>
                            <p class="mt-1 text-sm text-[#94766e]">Możesz spokojnie uzupełnić kalendarz.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <section class="dashboard-stat rounded-[1.75rem] border border-[#f0ddd7] bg-[linear-gradient(180deg,#fffefd_0%,#fff9f7_100%)] p-6 shadow-[0_12px_35px_rgba(219,193,186,0.12)]">
                    <p class="text-sm font-medium text-[#b37b70]">Dziś</p>
                    <p class="mt-4 text-4xl font-semibold text-[#2f2522]">{{ $stats['today_appointments'] }}</p>
                    <p class="mt-2 text-sm text-[#73615c]">wizyt zaplanowanych na dzisiaj</p>
                </section>

                <section class="dashboard-stat rounded-[1.75rem] border border-[#f0ddd7] bg-[linear-gradient(180deg,#fffefd_0%,#fff9f7_100%)] p-6 shadow-[0_12px_35px_rgba(219,193,186,0.12)]">
                    <p class="text-sm font-medium text-[#b37b70]">Następna wizyta</p>
                    @if ($nextAppointment)
                        <p class="mt-4 text-3xl font-semibold text-[#2f2522]">{{ $nextAppointment->starts_at->format('H:i') }}</p>
                        <p class="mt-2 text-sm text-[#5a4b46]">{{ $nextAppointment->client?->name ?? 'Klient' }}</p>
                        <p class="mt-1 text-sm text-[#8a7771]">{{ $nextAppointment->worker?->name ?? 'Pracownik' }}</p>
                    @else
                        <p class="mt-4 text-2xl font-semibold text-[#2f2522]">Brak</p>
                        <p class="mt-2 text-sm text-[#8a7771]">Nie ma kolejnych wizyt w kalendarzu.</p>
                    @endif
                </section>

                <section class="dashboard-stat dashboard-stat--soft-green rounded-[1.75rem] border border-[#e2ebe7] bg-[linear-gradient(180deg,#fcfffe_0%,#f4fbf8_100%)] p-6 shadow-[0_12px_35px_rgba(189,212,204,0.16)]">
                    <p class="text-sm font-medium text-[#689786]">SMS dziś</p>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#73615c]">Wysłane</span>
                            <span class="font-semibold text-[#4f8b7a]">{{ $stats['sms_sent_today'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#73615c]">Oczekujące</span>
                            <span class="font-semibold text-[#c78d47]">{{ $stats['sms_pending_today'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#73615c]">Błędne</span>
                            <span class="font-semibold text-[#c26a74]">{{ $stats['sms_failed_today'] }}</span>
                        </div>
                    </div>
                </section>

                <section class="dashboard-stat dashboard-stat--soft-violet rounded-[1.75rem] border border-[#eadcf1] bg-[linear-gradient(180deg,#fffefe_0%,#fbf7fd_100%)] p-6 shadow-[0_12px_35px_rgba(213,193,224,0.16)]">
                    <p class="text-sm font-medium text-[#9877a7]">Klienci</p>
                    <p class="mt-4 text-4xl font-semibold text-[#2f2522]">{{ $stats['clients_total'] }}</p>
                    <p class="mt-2 text-sm text-[#73615c]">łączna liczba klientów w bazie</p>
                </section>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_minmax(320px,1fr)]">
                <section class="dashboard-panel rounded-[1.75rem] border border-[#f0ddd7] bg-[linear-gradient(180deg,#fffefd_0%,#fffaf8_100%)] p-6 shadow-[0_12px_35px_rgba(219,193,186,0.12)]">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-semibold text-[#2f2522]">5 najbliższych wizyt</h3>
                            <p class="text-sm text-[#8a7771]">Kolejne pozycje w kalendarzu od teraz.</p>
                        </div>
                        <a href="{{ route('appointments.index') }}" class="rounded-full border border-[#ecd9d3] bg-white px-4 py-2 text-sm font-medium text-[#6a5651] transition hover:border-[#dcbfb7] hover:bg-[#fff7f4]">
                            Otwórz kalendarz
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($upcomingAppointments as $appointment)
                            <article class="dashboard-appointment rounded-[1.4rem] border border-[#f3e4df] bg-[linear-gradient(135deg,#fff8f6_0%,#fffdfc_48%,#f4fbf8_100%)] px-4 py-4 shadow-[0_8px_22px_rgba(232,213,207,0.14)]">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-lg font-semibold text-[#2f2522]">
                                            {{ $appointment->starts_at->format('H:i') }} · {{ $appointment->client?->name ?? 'Klient' }}
                                        </p>
                                        <p class="mt-1 text-sm text-[#6f5c57]">
                                            {{ $appointment->starts_at->locale('pl')->translatedFormat('d.m.Y') }}
                                            · {{ $appointment->worker?->name ?? 'Pracownik' }}
                                            · {{ $appointment->service?->name ?? 'Usługa' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#9d7f77] shadow-sm">
                                        {{ str_replace('_', ' ', $appointment->status) }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.4rem] border border-dashed border-[#e5ccc4] bg-[#fffaf8] px-4 py-8 text-center text-sm text-[#8c7670]">
                                Brak nadchodzących wizyt. Dodaj pierwszą z poziomu kalendarza.
                            </div>
                        @endforelse
                    </div>
                </section>

                <aside class="dashboard-panel dashboard-actions rounded-[1.75rem] border border-[#f0ddd7] bg-[linear-gradient(180deg,#fffefd_0%,#fffaf7_100%)] p-6 shadow-[0_12px_35px_rgba(219,193,186,0.12)]">
                    <h3 class="text-xl font-semibold text-[#2f2522]">Szybkie akcje</h3>
                    <p class="mt-1 text-sm text-[#8a7771]">Najczęściej używane przejścia w codziennej pracy.</p>

                    <div class="mt-5 space-y-3">
                        <a href="{{ route('appointments.index') }}" class="dashboard-action dashboard-action--primary block rounded-[1.4rem] bg-[linear-gradient(135deg,#e8b4ba_0%,#efc5b8_45%,#e8d4bc_100%)] px-4 py-4 text-[#2f2522] shadow-[0_12px_30px_rgba(220,182,180,0.24)] transition hover:translate-y-[-1px] hover:brightness-[0.99]">
                            <span class="block text-sm uppercase tracking-[0.2em] text-[#7a5953]">Kalendarz</span>
                            <span class="mt-1 block text-lg font-semibold">Dodaj lub przesuń wizytę</span>
                        </a>

                        <a href="{{ route('clients.create') }}" class="dashboard-action block rounded-[1.4rem] border border-[#ecd9d3] bg-[#fff6f2] px-4 py-4 transition hover:border-[#dcbfb7] hover:bg-[#fff0ea]">
                            <span class="block text-sm uppercase tracking-[0.2em] text-[#ad8b83]">Klienci</span>
                            <span class="mt-1 block text-lg font-semibold text-[#2f2522]">Dodaj klienta</span>
                        </a>

                        <a href="{{ route('services.index') }}" class="dashboard-action dashboard-action--green block rounded-[1.4rem] border border-[#dbe8e4] bg-[#f2fbf7] px-4 py-4 transition hover:border-[#bfd6cf] hover:bg-[#ebf7f2]">
                            <span class="block text-sm uppercase tracking-[0.2em] text-[#7f9d93]">Usługi</span>
                            <span class="mt-1 block text-lg font-semibold text-[#2f2522]">Zarządzaj ofertą</span>
                        </a>

                        <a href="{{ route('workers.index') }}" class="dashboard-action dashboard-action--violet block rounded-[1.4rem] border border-[#ece0ec] bg-[#faf5fb] px-4 py-4 transition hover:border-[#dbc7db] hover:bg-[#f5edf7]">
                            <span class="block text-sm uppercase tracking-[0.2em] text-[#a183a1]">Zespół</span>
                            <span class="mt-1 block text-lg font-semibold text-[#2f2522]">Sprawdź pracowników</span>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
