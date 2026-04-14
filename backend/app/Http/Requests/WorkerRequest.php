<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\Tenant;
use Illuminate\Validation\Validator;

class WorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'-]+$/u'],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('salon_id', Tenant::id())),
            ],
            'active' => ['boolean'],
            'color_hex' => ['nullable', 'string', 'max:9', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'schedule' => ['nullable', 'array'],
            'schedule.*.is_working' => ['nullable', 'boolean'],
            'schedule.*.start_time' => ['nullable', 'date_format:H:i'],
            'schedule.*.end_time' => ['nullable', 'date_format:H:i'],
            'time_offs' => ['nullable', 'array'],
            'time_offs.*.starts_at' => ['nullable', 'date'],
            'time_offs.*.ends_at' => ['nullable', 'date'],
            'time_offs.*.type' => ['nullable', 'string', 'max:40'],
            'time_offs.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $schedule = collect($this->input('schedule', []))
            ->map(fn ($row) => [
                'is_working' => filter_var($row['is_working'] ?? false, FILTER_VALIDATE_BOOL),
                'start_time' => $row['start_time'] ?? null,
                'end_time' => $row['end_time'] ?? null,
            ])
            ->toArray();

        $this->merge([
            'active' => $this->boolean('active'),
            'schedule' => $schedule,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('schedule', []) as $day => $row) {
                $isWorking = filter_var($row['is_working'] ?? false, FILTER_VALIDATE_BOOL);
                $startTime = $row['start_time'] ?? null;
                $endTime = $row['end_time'] ?? null;

                if ($isWorking && (!$startTime || !$endTime)) {
                    $validator->errors()->add("schedule.$day.start_time", 'Dla dnia pracy podaj godziny rozpoczęcia i zakończenia.');
                }

                if ($startTime && $endTime && $startTime >= $endTime) {
                    $validator->errors()->add("schedule.$day.end_time", 'Godzina zakończenia musi być późniejsza niż rozpoczęcia.');
                }
            }

            foreach ($this->input('time_offs', []) as $index => $row) {
                $startsAt = $row['starts_at'] ?? null;
                $endsAt = $row['ends_at'] ?? null;

                if (($startsAt && !$endsAt) || (!$startsAt && $endsAt)) {
                    $validator->errors()->add("time_offs.$index.starts_at", 'Dla nieobecności podaj początek i koniec.');
                }

                if ($startsAt && $endsAt && strtotime($startsAt) >= strtotime($endsAt)) {
                    $validator->errors()->add("time_offs.$index.ends_at", 'Koniec nieobecności musi być późniejszy niż początek.');
                }
            }
        });
    }
}
