<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerTimeOff extends Model
{
    use HasFactory;
    use HasSalon;

    protected $table = 'worker_time_off';

    protected $fillable = [
        'salon_id',
        'worker_id',
        'starts_at',
        'ends_at',
        'type',
        'note',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
