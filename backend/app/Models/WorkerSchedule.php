<?php

namespace App\Models;

use App\Models\Concerns\HasSalon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerSchedule extends Model
{
    use HasFactory;
    use HasSalon;

    protected $fillable = [
        'salon_id',
        'worker_id',
        'day_of_week',
        'is_working',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_working' => 'boolean',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
