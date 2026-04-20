<x-app-layout>
    <div class="settings-page py-12">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="settings-panel rounded-3xl border border-[#ecd8d1] bg-white/85 p-6 shadow-[0_20px_55px_rgba(214,188,180,0.16)] backdrop-blur">
                <h1 class="text-3xl font-semibold tracking-tight text-[#342824]">Ustawienia aplikacji</h1>
                <p class="mt-2 text-sm text-[#756562]">Godziny działania salonu i zachowanie interfejsu dla bieżącego konta.</p>
            </div>

            @if (session('status') === 'settings-updated')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Ustawienia zapisane.
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <section class="settings-panel rounded-3xl border border-[#ecd8d1] bg-white/88 p-6 shadow-[0_18px_40px_rgba(214,188,180,0.14)] backdrop-blur">
                    <h2 class="text-lg font-semibold text-[#342824]">Salon</h2>
                    <p class="mt-1 text-sm text-[#756562]">Dane podstawowe i zakres godzin widoczny w kalendarzu.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="company_name" value="Nazwa salonu" />
                            <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $salon?->name)" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="company_address" value="Adres" />
                            <x-text-input id="company_address" name="company_address" type="text" class="mt-1 block w-full" :value="old('company_address', $salon?->address)" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                        </div>

                        <div>
                            <x-input-label for="company_phone" value="Telefon" />
                            <x-text-input id="company_phone" name="company_phone" type="text" class="mt-1 block w-full" :value="old('company_phone', $salon?->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_phone')" />
                        </div>

                        <div>
                            <x-input-label for="company_email" value="E-mail salonu" />
                            <x-text-input id="company_email" name="company_email" type="email" class="mt-1 block w-full" :value="old('company_email', $salon?->email)" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                        </div>

                        <div>
                            <x-input-label for="opening_time" value="Otwarte od" />
                            <x-text-input
                                id="opening_time"
                                name="opening_time"
                                type="time"
                                class="mt-1 block w-full"
                                :value="old('opening_time', \Illuminate\Support\Str::of($salon?->opening_time ?? '09:00:00')->substr(0, 5))"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('opening_time')" />
                        </div>

                        <div>
                            <x-input-label for="closing_time" value="Otwarte do" />
                            <x-text-input
                                id="closing_time"
                                name="closing_time"
                                type="time"
                                class="mt-1 block w-full"
                                :value="old('closing_time', \Illuminate\Support\Str::of($salon?->closing_time ?? '17:00:00')->substr(0, 5))"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('closing_time')" />
                        </div>

                        <div class="sm:col-span-2 mt-2 rounded-2xl border border-[#ecd8d1] bg-[#fffaf7] p-4">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-[#9c746a]">Weekend</h3>
                            <p class="mt-1 text-sm text-[#756562]">Jeśli chcesz inne godziny na sobotę i niedzielę, ustaw je tutaj. Puste pola oznaczają użycie zwykłych godzin salonu.</p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-3 inline-flex items-center gap-3 text-sm text-[#4f403d]">
                                        <input type="hidden" name="saturday_closed" value="0">
                                        <input
                                            type="checkbox"
                                            name="saturday_closed"
                                            value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            {{ old('saturday_closed', $salon?->saturday_closed) ? 'checked' : '' }}
                                        >
                                        <span>Zamknięte w sobotę</span>
                                    </label>
                                    <x-input-label for="saturday_opening_time" value="Sobota od" />
                                    <x-text-input
                                        id="saturday_opening_time"
                                        name="saturday_opening_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        :value="old('saturday_opening_time', \Illuminate\Support\Str::of($salon?->saturday_opening_time ?? '')->substr(0, 5))"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('saturday_opening_time')" />
                                </div>

                                <div>
                                    <x-input-label for="saturday_closing_time" value="Sobota do" />
                                    <x-text-input
                                        id="saturday_closing_time"
                                        name="saturday_closing_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        :value="old('saturday_closing_time', \Illuminate\Support\Str::of($salon?->saturday_closing_time ?? '')->substr(0, 5))"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('saturday_closing_time')" />
                                </div>

                                <div>
                                    <label class="mb-3 inline-flex items-center gap-3 text-sm text-[#4f403d]">
                                        <input type="hidden" name="sunday_closed" value="0">
                                        <input
                                            type="checkbox"
                                            name="sunday_closed"
                                            value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            {{ old('sunday_closed', $salon?->sunday_closed) ? 'checked' : '' }}
                                        >
                                        <span>Zamknięte w niedzielę</span>
                                    </label>
                                    <x-input-label for="sunday_opening_time" value="Niedziela od" />
                                    <x-text-input
                                        id="sunday_opening_time"
                                        name="sunday_opening_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        :value="old('sunday_opening_time', \Illuminate\Support\Str::of($salon?->sunday_opening_time ?? '')->substr(0, 5))"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('sunday_opening_time')" />
                                </div>

                                <div>
                                    <x-input-label for="sunday_closing_time" value="Niedziela do" />
                                    <x-text-input
                                        id="sunday_closing_time"
                                        name="sunday_closing_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        :value="old('sunday_closing_time', \Illuminate\Support\Str::of($salon?->sunday_closing_time ?? '')->substr(0, 5))"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('sunday_closing_time')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel rounded-3xl border border-[#ecd8d1] bg-white/88 p-6 shadow-[0_18px_40px_rgba(214,188,180,0.14)] backdrop-blur">
                    <h2 class="text-lg font-semibold text-[#342824]">Interfejs</h2>
                    <p class="mt-1 text-sm text-[#756562]">Preferencje wyglądu i zachowania aplikacji dla bieżącego użytkownika.</p>

                    <div class="mt-5 space-y-5">
                        <div>
                            <x-input-label for="theme_mode" value="Motyw" />
                            <select id="theme_mode" name="theme_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="light" @selected(old('theme_mode', $user->theme_mode ?? 'light') === 'light')>Jasny</option>
                                <option value="dark" @selected(old('theme_mode', $user->theme_mode ?? 'light') === 'dark')>Ciemny</option>
                            </select>
                            <p class="mt-2 text-sm text-[#756562]">Proponowany ciemny: grafitowe tło, lekko różowe akcenty i jaśniejsze karty dla kontrastu.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('theme_mode')" />
                        </div>

                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <x-primary-button>Zapisz ustawienia</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
