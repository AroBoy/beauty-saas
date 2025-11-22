<?php

namespace App\Rules;

use App\Support\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class BelongsToTenant implements ValidationRule
{
    /**
     * @param class-string<Model> $modelClass
     */
    public function __construct(
        protected string $modelClass,
        protected string $column = 'id'
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Tenant::has()) {
            $fail('Brak kontekstu salonu.');

            return;
        }

        /** @var Model $model */
        $model = $this->modelClass;

        $found = $model::query()
            ->where($this->column, $value)
            ->where('salon_id', Tenant::id())
            ->exists();

        if (!$found) {
            $fail('Wybrany rekord nie należy do bieżącego salonu.');
        }
    }
}
