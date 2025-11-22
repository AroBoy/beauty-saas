<?php

namespace App\Models\Concerns;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait HasSalon
{
    protected static function bootHasSalon(): void
    {
        static::creating(function ($model) {
            if (!$model->getAttribute('salon_id') && Tenant::has()) {
                $model->setAttribute('salon_id', Tenant::id());
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Tenant::has()) {
                $builder->where($builder->qualifyColumn('salon_id'), Tenant::id());
            }
        });
    }

    public function scopeForCurrentTenant(Builder $builder): Builder
    {
        return Tenant::has()
            ? $builder->where($builder->qualifyColumn('salon_id'), Tenant::id())
            : $builder;
    }
}
