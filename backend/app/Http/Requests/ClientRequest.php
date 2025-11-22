<?php

namespace App\Http\Requests;

use App\Support\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'-]+$/u'],
            'phone' => [
                'nullable',
                'string',
                'max:32',
                'regex:/^[0-9+\s-]{6,20}$/',
                Rule::unique('clients', 'phone')->where(fn ($q) => $q->where('salon_id', Tenant::id()))->ignore($this->client?->id),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')->where(fn ($q) => $q->where('salon_id', Tenant::id()))->ignore($this->client?->id),
            ],
            'notes' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
