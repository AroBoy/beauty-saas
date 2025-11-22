<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    use HasSalon;

    protected $fillable = [
        'salon_id',
        'name',
        'duration_min',
        'price',
        'active',
    ];

    protected $casts = [
        'duration_min' => 'integer',
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
