<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'default_visit_length_min',
        'sms_sender',
        'sms_reminder_hours',
    ];

    protected $casts = [
        'default_visit_length_min' => 'integer',
        'sms_reminder_hours' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
