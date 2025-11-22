<?php

namespace App\Http\Requests;

use App\Support\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s0-9\'"-]+$/u',
                Rule::unique('services', 'name')->where(fn ($q) => $q->where('salon_id', Tenant::id()))->ignore($this->service?->id),
            ],
            'duration_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }
}
