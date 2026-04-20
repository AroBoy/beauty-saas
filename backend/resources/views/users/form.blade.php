<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $isEdit ? 'Edytuj operatora' : 'Dodaj operatora' }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $isEdit ? 'Zmień dane logowania operatora przypisanego do tego salonu.' : 'Utwórz nowe konto operatora dla bieżącego salonu.' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form
                    method="POST"
                    action="{{ $isEdit ? route('users.update', $userModel) : route('users.store') }}"
                    class="space-y-5"
                >
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="name" value="Imię i nazwisko" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $userModel->name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="E-mail logowania" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $userModel->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="password" :value="$isEdit ? 'Nowe hasło' : 'Hasło'" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $isEdit" />
                            @if($isEdit)
                                <p class="mt-2 text-xs text-gray-500">Zostaw puste, jeśli hasło ma zostać bez zmian.</p>
                            @endif
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" value="Powtórz hasło" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $isEdit" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>
                            {{ $isEdit ? 'Zapisz zmiany' : 'Utwórz operatora' }}
                        </x-primary-button>

                        <a href="{{ route('users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Wróć do listy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
