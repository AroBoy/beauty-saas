<div
    id="edit-appointment-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center"
    data-update-url="{{ isset($appointment) ? route('appointments.update', $appointment) : '' }}"
    data-id="{{ $appointment->id ?? '' }}"
>
    <div class="fixed inset-0 bg-black/30" id="edit-backdrop"></div>
    <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Edytuj wizytę</h3>
                <p class="text-sm text-gray-500">Zaktualizuj pracownika, klienta i godzinę.</p>
            </div>
            <button type="button" id="edit-close" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="edit-appointment-form" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-appointment-id" name="appointment_id">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pracownik</label>
                    <select name="worker_id" id="edit-worker" class="mt-1 block w-full rounded border-gray-300 text-sm">
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">Klient</label>
                    <input type="hidden" name="client_id" id="edit-client-id">
                    <input type="text" id="edit-client-search" class="mt-1 block w-full rounded border-gray-300 text-sm"
                           placeholder="Wpisz imię lub telefon" autocomplete="off">
                    <ul id="edit-client-suggestions"
                        class="absolute z-50 mt-1 hidden w-full rounded border border-gray-200 bg-white shadow">
                    </ul>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Usługa</label>
                    <select name="service_id" id="edit-service" class="mt-1 block w-full rounded border-gray-300 text-sm">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" data-duration="{{ $service->duration_min }}">{{ $service->name }} ({{ $service->duration_min }}m)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start</label>
                    <input type="text" id="edit-start" name="starts_at" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Czas (min)</label>
                    <input type="number" min="1" id="edit-duration" name="duration_min" value="30" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cena</label>
                    <input type="number" step="0.01" id="edit-price" name="price_charged" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Zapisz</button>
                <button type="button" id="edit-delete" class="rounded border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Usuń</button>
                <button type="button" id="edit-cancel" class="text-sm text-gray-600 hover:text-gray-800">Anuluj</button>
            </div>
        </form>
    </div>
</div>
