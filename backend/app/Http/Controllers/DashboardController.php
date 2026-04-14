<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\SmsJob;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $todayStart = CarbonImmutable::now()->startOfDay();
        $todayEnd = $todayStart->endOfDay();

        $todayAppointments = Appointment::query()
            ->whereBetween('starts_at', [$todayStart, $todayEnd])
            ->where('status', '!=', 'cancelled');

        $nextAppointment = Appointment::query()
            ->with(['client', 'worker', 'service'])
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();

        $upcomingAppointments = Appointment::query()
            ->with(['client', 'worker', 'service'])
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $smsBaseQuery = SmsJob::query()->whereBetween('send_at', [$todayStart, $todayEnd]);

        return view('dashboard', [
            'stats' => [
                'today_appointments' => (clone $todayAppointments)->count(),
                'clients_total' => Client::query()->count(),
                'sms_sent_today' => (clone $smsBaseQuery)->where('status', 'sent')->count(),
                'sms_pending_today' => (clone $smsBaseQuery)->where('status', 'pending')->count(),
                'sms_failed_today' => (clone $smsBaseQuery)->where('status', 'failed')->count(),
            ],
            'todayLabel' => $todayStart->locale('pl')->translatedFormat('l, d.m.Y'),
            'nextAppointment' => $nextAppointment,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }
}
