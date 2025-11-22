<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    use HasSalon;

    protected $fillable = [
        'salon_id',
        'name',
        'phone',
        'email',
        'notes',
        'avatar_path',
    ];

    protected $appends = ['avatar_url'];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function consents()
    {
        return $this->hasMany(ClientConsent::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar_path) {
            return null;
        }

        return asset('storage/'.$this->avatar_path);
    }
}
