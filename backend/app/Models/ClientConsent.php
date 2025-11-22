<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientConsent extends Model
{
    use HasFactory;
    use HasSalon;

    protected $fillable = [
        'salon_id',
        'client_id',
        'consent_type',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
