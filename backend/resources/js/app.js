import './bootstrap';

import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { Polish } from 'flatpickr/dist/l10n/pl.js';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const date = calendarEl.dataset.date;
    const apiBase = (calendarEl.dataset.apiBase || window.location.origin).replace(/\/$/, '');
    const buildUrl = (u) => {
        if (!u) return '';
        if (/^https?:\/\//i.test(u)) return u; // already absolute
        return `${apiBase}${u.startsWith('/') ? '' : '/'}${u}`;
    };
    const feedUrl = buildUrl(calendarEl.dataset.feed);
    const resourcesUrl = buildUrl(calendarEl.dataset.resources);
    const moveBase = buildUrl(calendarEl.dataset.move);
    const modal = document.getElementById('quick-appointment-modal');
    const backdrop = document.getElementById('quick-appointment-backdrop');
    const form = document.getElementById('quick-appointment-form');
    const startInput = document.getElementById('qa-start');
    const durationInput = document.getElementById('qa-duration');
    const workerSelect = document.getElementById('qa-worker');
    const clientIdInput = document.getElementById('qa-client-id');
    const clientSearchInput = document.getElementById('qa-client-search');
    const clientSuggestions = document.getElementById('qa-client-suggestions');
    const serviceSelect = document.getElementById('qa-service');
    const priceInput = document.getElementById('qa-price');
    const closeBtns = [document.getElementById('qa-close'), document.getElementById('qa-cancel')];
    const openBtn = document.getElementById('qa-open');
    const prevDayBtn = document.getElementById('prev-day');
    const nextDayBtn = document.getElementById('next-day');
    const todayBtn = document.getElementById('today');
    const datePickerInput = document.getElementById('date-picker');
    const clientsSearchUrl = buildUrl(calendarEl.dataset.clientsSearch);
    let searchTimeout = null;
    let suggestionItems = [];
    let suggestionIndex = -1;
    const startPicker = flatpickr(startInput, {
        enableTime: true,
        time_24hr: true,
        minuteIncrement: 5,
        altInput: true,
        altFormat: 'd.m.Y, H:i',
        dateFormat: 'Y-m-d H:i',
        defaultDate: `${date} 09:00`,
        locale: Polish,
    });

    // Edit modal refs
    const editModal = document.getElementById('edit-appointment-modal');
    const editBackdrop = document.getElementById('edit-backdrop');
    const editForm = document.getElementById('edit-appointment-form');
    const editAppointmentIdInput = document.getElementById('edit-appointment-id');
    const editStartInput = document.getElementById('edit-start');
    const editDurationInput = document.getElementById('edit-duration');
    const editWorkerSelect = document.getElementById('edit-worker');
    const editServiceSelect = document.getElementById('edit-service');
    const editPriceInput = document.getElementById('edit-price');
    const editClientIdInput = document.getElementById('edit-client-id');
    const editClientSearchInput = document.getElementById('edit-client-search');
    const editClientSuggestions = document.getElementById('edit-client-suggestions');
    const editDeleteBtn = document.getElementById('edit-delete');
    const editCloseBtns = [document.getElementById('edit-close'), document.getElementById('edit-cancel'), editBackdrop];
    let editSuggestionItems = [];
    let editSuggestionIndex = -1;
    const editStartPicker = editStartInput ? flatpickr(editStartInput, {
        enableTime: true,
        time_24hr: true,
        minuteIncrement: 5,
        altInput: true,
        altFormat: 'd.m.Y, H:i',
        dateFormat: 'Y-m-d H:i',
        defaultDate: `${date} 09:00`,
        locale: Polish,
    }) : null;

    let calendar;

    const formatYMD = (d) => {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    };

    calendar = new Calendar(calendarEl, {
        plugins: [interactionPlugin, timeGridPlugin, resourceTimeGridPlugin],
        initialView: 'resourceTimeGridDay',
        initialDate: date,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '00:15:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        headerToolbar: false,
        allDaySlot: false,
        editable: true,
        droppable: false,
        eventOverlap: false,
        height: 'auto',
        resourceAreaHeaderContent: 'Pracownicy',
        selectable: true,
        resources: {
            url: resourcesUrl,
            method: 'GET',
            extraParams() {
                const current = calendar?.getDate() || new Date(date);
                return { date: formatYMD(current) };
            },
            failure: () => alert('Nie udało się załadować pracowników.'),
        },
        events: {
            url: feedUrl,
            method: 'GET',
            extraParams() {
                const current = calendar?.getDate() || new Date(date);
                return { date: formatYMD(current) };
            },
            failure: () => alert('Nie udało się załadować wizyt.'),
        },
        dateClick: info => openModal(info),
        eventDrop: info => handleChange(info),
        eventResize: info => handleChange(info),
        eventClick: info => {
            if (editModal) {
                openEditModal(info.event);
            } else {
                window.location.href = `${moveBase}/${info.event.id}/edit`;
            }
        },
    });

    function handleChange(info) {
        fetch(`${moveBase}/${info.event.id}/move`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                worker_id: info.newResource ? info.newResource.id : info.event.getResources()[0].id,
                starts_at: info.event.start.toISOString(),
                duration_min: (info.event.end - info.event.start) / 60000,
            }),
        }).then(resp => {
            if (!resp.ok) throw new Error('Błąd zapisu');
        }).catch(() => info.revert());
    }

    calendar.render();

    function openModal(info) {
        const start = info.date || info.start;
        startPicker.setDate(start, true);
        durationInput.value = durationInput.value || 30;
        clientIdInput.value = '';
        clientSearchInput.value = '';
        clientSuggestions.classList.add('hidden');
        suggestionItems = [];
        suggestionIndex = -1;
        if (info.resource) {
            workerSelect.value = info.resource.id;
        }
        const selectedService = serviceSelect.selectedOptions[0];
        if (selectedService && selectedService.dataset.duration) {
            durationInput.value = selectedService.dataset.duration;
        }
        backdrop.classList.remove('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        backdrop.classList.add('hidden');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function changeDay(offset) {
        const current = calendar.getDate();
        const target = new Date(current);
        target.setDate(current.getDate() + offset);
        calendar.gotoDate(target);
        refreshDayInputs();
        calendar.refetchResources();
        calendar.refetchEvents();
    }

    function refreshDayInputs() {
        const current = calendar.getDate();
        const iso = formatYMD(current);
        datePickerInput.value = iso;
        const label = document.getElementById('calendar-day-label');
        if (label) {
            label.textContent = `(${new Intl.DateTimeFormat('pl-PL').format(current)})`;
        }
        startPicker.setDate(`${iso} ${startPicker.selectedDates[0]?.toTimeString().slice(0,5) || '09:00'}`);
        editStartPicker?.setDate(`${iso} ${editStartPicker?.selectedDates[0]?.toTimeString().slice(0,5) || '09:00'}`);
    }

    closeBtns.forEach(btn => btn?.addEventListener('click', closeModal));
    backdrop?.addEventListener('click', closeModal);
    openBtn?.addEventListener('click', () => {
        const defaultStart = new Date(`${date}T09:00`);
        openModal({ date: defaultStart, resource: null });
    });

    prevDayBtn?.addEventListener('click', () => {
        changeDay(-1);
    });
    nextDayBtn?.addEventListener('click', () => {
        changeDay(1);
    });
    todayBtn?.addEventListener('click', () => {
        calendar.gotoDate(new Date());
        refreshDayInputs();
        calendar.refetchResources();
        calendar.refetchEvents();
    });

    const dateInputPicker = datePickerInput ? flatpickr(datePickerInput, {
        dateFormat: 'Y-m-d',
        defaultDate: date,
        locale: Polish,
        onChange: (selectedDates) => {
            if (selectedDates[0]) {
                calendar.gotoDate(selectedDates[0]);
                refreshDayInputs();
                calendar.refetchResources();
                calendar.refetchEvents();
            }
        },
    }) : null;

    serviceSelect?.addEventListener('change', () => {
        const selected = serviceSelect.selectedOptions[0];
        const dur = selected?.dataset.duration;
        if (dur) {
            durationInput.value = dur;
        }
    });

    const normalizePrice = (value) => {
        if (!value) return null;
        const normalized = value.replace(',', '.').trim();
        const num = Number(normalized);
        return Number.isFinite(num) ? num : null;
    };

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            worker_id: workerSelect.value,
            client_id: clientIdInput.value,
            service_id: serviceSelect.value,
            starts_at: startInput.value,
            duration_min: durationInput.value,
            price_charged: normalizePrice(priceInput.value),
            status: 'planned',
        };

        const resp = await fetch(`${moveBase}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(payload),
        });

        if (!resp.ok) {
            const msg = await resp.text().catch(() => '');
            alert(`Nie udało się zapisać wizyty. (${resp.status}) ${msg}`);
            return;
        }

        closeModal();
        calendar.refetchEvents();
    });

    clientSearchInput?.addEventListener('input', () => {
        if (!clientsSearchUrl) return;
        const term = clientSearchInput.value.trim();
        if (searchTimeout) clearTimeout(searchTimeout);
        if (!term) {
            clientSuggestions.classList.add('hidden');
            return;
        }
        searchTimeout = setTimeout(async () => {
            try {
                const resp = await fetch(`${clientsSearchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!resp.ok) {
                    console.error('Search clients failed', resp.status);
                    return;
                }
                const data = await resp.json();
                renderSuggestions(data);
            } catch (e) {
                console.error('Search clients error', e);
            }
        }, 200);
    });

    clientSearchInput?.addEventListener('keydown', (e) => {
        if (clientSuggestions.classList.contains('hidden')) return;
        if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(e.key)) return;
        e.preventDefault();
        if (e.key === 'Escape') {
            clientSuggestions.classList.add('hidden');
            return;
        }
        if (e.key === 'Enter' && suggestionIndex >= 0 && suggestionItems[suggestionIndex]) {
            selectSuggestion(suggestionItems[suggestionIndex]);
            return;
        }
        if (e.key === 'ArrowDown') {
            if (!suggestionItems.length) return;
            suggestionIndex = (suggestionIndex + 1) % suggestionItems.length;
            highlightSuggestion();
        }
        if (e.key === 'ArrowUp') {
            if (!suggestionItems.length) return;
            suggestionIndex = suggestionIndex <= 0 ? suggestionItems.length - 1 : suggestionIndex - 1;
            highlightSuggestion();
        }
    });

    function renderSuggestions(items) {
        if (!items.length) {
            clientSuggestions.innerHTML = '<li class="px-3 py-2 text-sm text-gray-500">Brak wyników</li>';
            clientSuggestions.classList.remove('hidden');
            suggestionItems = [];
            suggestionIndex = -1;
            return;
        }
        clientSuggestions.innerHTML = '';
        items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.label;
            li.dataset.id = item.id;
            li.className = 'cursor-pointer px-3 py-2 text-sm hover:bg-gray-100';
            li.addEventListener('click', () => {
                selectSuggestion(li);
            });
            clientSuggestions.appendChild(li);
        });
        suggestionItems = Array.from(clientSuggestions.querySelectorAll('li[data-id]'));
        suggestionIndex = -1;
        clientSuggestions.classList.remove('hidden');
    }

    function highlightSuggestion() {
        suggestionItems.forEach((li, idx) => {
            li.classList.toggle('bg-indigo-50', idx === suggestionIndex);
            li.classList.toggle('text-indigo-700', idx === suggestionIndex);
        });
    }

    function selectSuggestion(li) {
        clientIdInput.value = li.dataset.id;
        clientSearchInput.value = li.textContent;
        clientSuggestions.classList.add('hidden');
        suggestionIndex = -1;
    }

    // Edit modal helpers
    function openEditModal(event) {
        if (!editModal) return;
        editModal.dataset.id = event.id;
        editModal.dataset.updateUrl = `${window.location.origin}/appointments/${event.id}`;
        if (editAppointmentIdInput) editAppointmentIdInput.value = event.id;
        editWorkerSelect.value = event.getResources()[0]?.id || '';
        editServiceSelect.value = event.extendedProps.service_id || '';
        editDurationInput.value = event.extendedProps.duration_min || (event.end ? (event.end - event.start) / 60000 : 30);
        editPriceInput.value = event.extendedProps.price || '';
        editClientIdInput.value = event.extendedProps.client_id || '';
        editClientSearchInput.value = event.title;
        editStartPicker?.setDate(event.start, true);
        editBackdrop.classList.remove('hidden');
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    }

    function closeEditModal() {
        if (!editModal) return;
        editBackdrop.classList.add('hidden');
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    }

    editCloseBtns.forEach(btn => btn?.addEventListener('click', closeEditModal));

    if (!editDeleteBtn) {
        console.warn('Brak przycisku usuwania wizyty (#edit-delete)');
    } else {
        editDeleteBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!editModal) return;
            const appointmentId = editModal.dataset.id;
            if (!appointmentId) {
                console.warn('Brak id wizyty w modalu');
                return;
            }
            if (!confirm('Usunąć wizytę?')) return;
            try {
                console.log('Usuwam wizytę', appointmentId);
                const resp = await fetch(`${moveBase}/${appointmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    credentials: 'include',
                });
                if (!resp.ok) {
                    const text = await resp.text();
                    console.error('Delete failed', resp.status, text);
                    alert('Nie udało się usunąć wizyty.');
                    return;
                }
                closeEditModal();
                calendar.refetchEvents();
            } catch (err) {
                console.error('Delete appointment error', err);
                alert('Nie udało się usunąć wizyty.');
            }
        });
    }

    editServiceSelect?.addEventListener('change', () => {
        const selected = editServiceSelect.selectedOptions[0];
        const dur = selected?.dataset.duration;
        if (dur) editDurationInput.value = dur;
    });

    editForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const appointmentId = editAppointmentIdInput?.value || editModal?.dataset?.id;
        const updateUrl = appointmentId ? `${moveBase}/${appointmentId}` : moveBase;
        console.debug('Updating appointment', { appointmentId, updateUrl, payloadPreview: editStartInput.value });
        const payload = {
            worker_id: editWorkerSelect.value,
            client_id: editClientIdInput.value,
            service_id: editServiceSelect.value,
            starts_at: editStartInput.value,
            duration_min: editDurationInput.value,
            price_charged: normalizePrice(editPriceInput.value),
            status: 'planned',
            appointment_id: appointmentId || null,
        };

        const resp = await fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(payload),
        });

        if (!resp.ok) {
            const msg = await resp.text().catch(() => '');
            alert(`Nie udało się zapisać wizyty. (${resp.status}) ${msg}`);
            return;
        }

        closeEditModal();
        calendar.refetchEvents();
    });

    editClientSearchInput?.addEventListener('input', () => {
        if (!clientsSearchUrl) return;
        const term = editClientSearchInput.value.trim();
        if (searchTimeout) clearTimeout(searchTimeout);
        if (!term) {
            editClientSuggestions.classList.add('hidden');
            return;
        }
        searchTimeout = setTimeout(async () => {
            try {
                const resp = await fetch(`${clientsSearchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!resp.ok) return;
                const data = await resp.json();
                renderEditSuggestions(data);
            } catch (e) {
                console.error('Search clients error', e);
            }
        }, 200);
    });

    function renderEditSuggestions(items) {
        if (!items.length) {
            editClientSuggestions.innerHTML = '<li class="px-3 py-2 text-sm text-gray-500">Brak wyników</li>';
            editClientSuggestions.classList.remove('hidden');
            editSuggestionItems = [];
            editSuggestionIndex = -1;
            return;
        }
        editClientSuggestions.innerHTML = '';
        items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.label;
            li.dataset.id = item.id;
            li.className = 'cursor-pointer px-3 py-2 text-sm hover:bg-gray-100';
            li.addEventListener('click', () => selectEditSuggestion(li));
            editClientSuggestions.appendChild(li);
        });
        editSuggestionItems = Array.from(editClientSuggestions.querySelectorAll('li[data-id]'));
        editSuggestionIndex = -1;
        editClientSuggestions.classList.remove('hidden');
    }

    editClientSearchInput?.addEventListener('keydown', (e) => {
        if (editClientSuggestions.classList.contains('hidden')) return;
        if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(e.key)) return;
        e.preventDefault();
        if (e.key === 'Escape') {
            editClientSuggestions.classList.add('hidden');
            return;
        }
        if (e.key === 'Enter' && editSuggestionIndex >= 0 && editSuggestionItems[editSuggestionIndex]) {
            selectEditSuggestion(editSuggestionItems[editSuggestionIndex]);
            return;
        }
        if (e.key === 'ArrowDown') {
            if (!editSuggestionItems.length) return;
            editSuggestionIndex = (editSuggestionIndex + 1) % editSuggestionItems.length;
            highlightEditSuggestion();
        }
        if (e.key === 'ArrowUp') {
            if (!editSuggestionItems.length) return;
            editSuggestionIndex = editSuggestionIndex <= 0 ? editSuggestionItems.length - 1 : editSuggestionIndex - 1;
            highlightEditSuggestion();
        }
    });

    function highlightEditSuggestion() {
        editSuggestionItems.forEach((li, idx) => {
            li.classList.toggle('bg-indigo-50', idx === editSuggestionIndex);
            li.classList.toggle('text-indigo-700', idx === editSuggestionIndex);
        });
    }

    function selectEditSuggestion(li) {
        editClientIdInput.value = li.dataset.id;
        editClientSearchInput.value = li.textContent;
        editClientSuggestions.classList.add('hidden');
        editSuggestionIndex = -1;
    }
});
