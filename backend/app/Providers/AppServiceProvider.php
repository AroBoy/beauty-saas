<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ClientConsent;
use App\Models\Service;
use App\Models\SmsJob;
use App\Models\Worker;
use App\Support\Tenant;
use App\Services\Sms\FakeSmsGateway;
use App\Services\Sms\SmsapiGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function () {
            $driver = config('sms.driver');

            return match ($driver) {
                'smsapi' => new SmsapiGateway(
                    token: config('sms.smsapi.token'),
                    endpoint: config('sms.smsapi.endpoint')
                ),
                default => new FakeSmsGateway(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $resolveTenant = static function (): void {
            if (Tenant::has()) {
                return;
            }

            $tenantId = optional(request()->user())->salon_id ?? request()->session()->get('salon_id');

            if ($tenantId) {
                Tenant::set((int) $tenantId);
            }
        };

        $bindTenantModel = static function (string $param, string $model) use ($resolveTenant): void {
            Route::bind($param, function ($value) use ($model, $resolveTenant) {
                $resolveTenant();
                abort_unless(Tenant::has(), 401);

                return $model::query()
                    ->where('salon_id', Tenant::id())
                    ->findOrFail($value);
            });
        };

        $bindTenantModel('worker', Worker::class);
        $bindTenantModel('client', Client::class);
        $bindTenantModel('clientConsent', ClientConsent::class);
        $bindTenantModel('service', Service::class);
        $bindTenantModel('appointment', Appointment::class);
        $bindTenantModel('smsJob', SmsJob::class);
    }
}
