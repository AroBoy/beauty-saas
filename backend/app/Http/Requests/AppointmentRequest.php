<?php

namespace App\Http\Requests;

use App\Models\Client;
use App\Models\Service;
use App\Models\Worker;
use App\Rules\BelongsToTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuses = ['planned', 'confirmed', 'cancelled', 'no_show', 'completed'];

        return [
            'worker_id' => ['required', new BelongsToTenant(Worker::class)],
            'client_id' => ['required', new BelongsToTenant(Client::class)],
            'service_id' => ['required', new BelongsToTenant(Service::class)],
            'starts_at' => ['required', 'date'],
            'duration_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'status' => ['nullable', Rule::in($statuses)],
            'price_charged' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
