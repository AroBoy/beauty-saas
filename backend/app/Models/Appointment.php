<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    use HasSalon;

    protected $fillable = [
        'salon_id',
        'worker_id',
        'client_id',
        'service_id',
        'starts_at',
        'duration_min',
        'status',
        'price_charged',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'duration_min' => 'integer',
        'price_charged' => 'decimal:2',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function smsJobs()
    {
        return $this->hasMany(SmsJob::class);
    }
}
