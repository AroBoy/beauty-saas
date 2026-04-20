<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppSettingsUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:32'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i'],
            'saturday_opening_time' => ['nullable', 'date_format:H:i'],
            'saturday_closing_time' => ['nullable', 'date_format:H:i'],
            'sunday_opening_time' => ['nullable', 'date_format:H:i'],
            'sunday_closing_time' => ['nullable', 'date_format:H:i'],
            'saturday_closed' => ['nullable', 'boolean'],
            'sunday_closed' => ['nullable', 'boolean'],
            'theme_mode' => ['required', Rule::in(['light', 'dark'])],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $openingTime = $this->input('opening_time');
                $closingTime = $this->input('closing_time');

                if (!$openingTime || !$closingTime) {
                    return;
                }

                if ($closingTime <= $openingTime) {
                    $validator->errors()->add('closing_time', 'Godzina zamknięcia musi być późniejsza niż godzina otwarcia.');
                }

                $this->validateDayRange($validator, 'saturday_opening_time', 'saturday_closing_time', 'soboty', (bool) $this->input('saturday_closed'));
                $this->validateDayRange($validator, 'sunday_opening_time', 'sunday_closing_time', 'niedzieli', (bool) $this->input('sunday_closed'));
            },
        ];
    }

    protected function validateDayRange($validator, string $openingKey, string $closingKey, string $label, bool $closed): void
    {
        if ($closed) {
            return;
        }

        $openingTime = $this->input($openingKey);
        $closingTime = $this->input($closingKey);

        if (!$openingTime && !$closingTime) {
            return;
        }

        if (!$openingTime || !$closingTime) {
            $validator->errors()->add($openingKey, "Podaj komplet godzin dla {$label} albo zostaw oba pola puste.");
            return;
        }

        if ($closingTime <= $openingTime) {
            $validator->errors()->add($closingKey, "Godzina zamknięcia dla {$label} musi być późniejsza niż otwarcie.");
        }
    }
}
