<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="rounded-2xl border border-[#ecd9d3] bg-[#fffaf7] p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[#9c746a]">Nowa firma</h2>
            <p class="mt-1 text-sm text-gray-600">Założysz salon oraz główne konto administratora.</p>

            <div class="mt-4 grid gap-4">
                <div>
                    <x-input-label for="company_name" value="Nazwa firmy" />
                    <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" required autofocus autocomplete="organization" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="company_address" value="Adres firmy" />
                    <x-text-input id="company_address" class="block mt-1 w-full" type="text" name="company_address" :value="old('company_address')" autocomplete="street-address" />
                    <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="company_phone" value="Telefon firmowy" />
                        <x-text-input id="company_phone" class="block mt-1 w-full" type="text" name="company_phone" :value="old('company_phone')" autocomplete="tel" />
                        <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="company_email" value="E-mail firmowy" />
                        <x-text-input id="company_email" class="block mt-1 w-full" type="email" name="company_email" :value="old('company_email')" autocomplete="email" />
                        <x-input-error :messages="$errors->get('company_email')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="sms_sender" value="Nazwa nadawcy SMS" />
                    <x-text-input id="sms_sender" class="block mt-1 w-full" type="text" name="sms_sender" :value="old('sms_sender')" maxlength="11" />
                    <p class="mt-1 text-xs text-gray-500">Opcjonalnie. Ustaw tylko jeśli masz zatwierdzone pole nadawcy w SMSAPI.</p>
                    <x-input-error :messages="$errors->get('sms_sender')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-[#ecd9d3] bg-white p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[#9c746a]">Konto administratora</h2>

            <div class="mt-4 grid gap-4">
                <div>
                    <x-input-label for="name" value="Imię i nazwisko" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="E-mail logowania" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="mt-6">
            <x-input-label for="password" value="Hasło" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Powtórz hasło" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Masz już konto?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Załóż konto') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
