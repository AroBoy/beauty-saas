# Beauty SaaS – tenant-aware design (Laravel + Postgres)

## Multi-tenant model
- Single database, shared schema; every business table has `salon_id` (FK to `salons.id`).
- `TenantMiddleware` resolves current tenant from authenticated user (`auth()->user()->salon_id`) or session token and stores it in request container.
- `HasSalon` Eloquent trait adds:
  - `$fillable` / `$casts` additions for `salon_id`.
  - Global scope to automatically add `where salon_id = current()` to queries.
  - `bootHasSalon` hook to set `salon_id` on create.
- Queued jobs store `salon_id` in payload; job `handle()` sets current tenant before running (so reminders respect tenant).
- Policies/gates check both role and `salon_id` match.

Example middleware sketch:
```php
class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenantId = optional($request->user())->salon_id ?? $request->session()->get('salon_id');
        abort_unless($tenantId, 401);
        app()->instance('tenant.id', $tenantId);
        return $next($request);
    }
}
```
Global scope extractor:
```php
trait HasSalon
{
    protected static function bootHasSalon()
    {
        static::creating(fn($model) => $model->salon_id = app('tenant.id'));
        static::addGlobalScope('tenant', fn(Builder $q) => $q->where('salon_id', app('tenant.id')));
    }
}
```

## Database tables (MVP)
- `salons`: `id`, `name`, `address`, `phone`, `email`, `default_visit_length_min?`, SMS sender, reminder_hours, timestamps.
- `users`: `id`, `salon_id`, `name`, `email`, `password`, `role` (`owner|manager|worker`), `remember_token`, timestamps.
- `workers`: `id`, `salon_id`, `user_id?`, `name`, `active`, `color_hex`, timestamps.
- `clients`: `id`, `salon_id`, `name`, `phone`, `email`, `notes`, timestamps (index `salon_id,phone` for lookup).
- `client_consents`: `id`, `client_id` (FK), `consent_type`, `granted_at`, `revoked_at`, timestamps.
- `services`: `id`, `salon_id`, `name`, `duration_min`, `price` (decimal 10,2), `active`, timestamps.
- `appointments`: `id`, `salon_id`, `worker_id`, `client_id`, `service_id`, `starts_at`, `duration_min`, `status` (`planned|confirmed|cancelled|no_show|completed`), `price_charged`, `notes`, `created_by_user_id`, timestamps. Unique index to avoid overlaps per worker (see logic below).
- `sms_jobs`: `id`, `salon_id`, `appointment_id`, `to_phone`, `type` (`booking_confirmation|reminder`), `send_at`, `sent_at?`, `status` (`pending|sent|cancelled|failed`), `message_body`, `failure_reason?`, timestamps.
- Future: `worker_schedules` (weekday, start_time, end_time, salon_id, worker_id, active_from, active_until).

Migration ordering: salons → users → workers → clients → client_consents → services → appointments → sms_jobs. Use cascading deletes where safe (e.g., client deletes cascade consents, but appointments should restrict delete if linked data should be preserved; prefer soft deletes for audit if needed).

## Appointment safety (no overlaps)
- DB check with exclusion-like constraint alternative for MySQL-compatible approach: before storing, validate no existing appointment for same `worker_id` where time ranges overlap.
- Add DB unique index on (`worker_id`, `starts_at`) for fast lookups; conflict detection happens in service layer.
- Service method: `AppointmentService::create` runs transaction: validate worker availability -> create appointment -> enqueue SMS jobs.

## Jobs and scheduler (SMS)
- On create: enqueue two `sms_jobs` rows (`booking_confirmation` with `send_at = now()`, `reminder` with `send_at = starts_at - reminder_hours`).
- On reschedule: update related `sms_jobs` send_at for reminders.
- On cancel/delete: set related pending jobs to `cancelled`.
- Scheduler (artisan command in `Kernel` every minute): fetch pending jobs where `send_at <= now()`; lock rows `for update skip locked`; send via SMS gateway client; mark `sent_at` or `failed`.
- Job payload carries `salon_id` to ensure tenant context is restored before querying data used for SMS body.

## Core HTTP surface (draft routes)
- Auth: Laravel Breeze/Fortify; login issues session with tenant derived from user.
- `/dashboard` – summary per salon.
- `/workers` CRUD.
- `/clients` CRUD + quick create modal in calendar (phone/name search endpoint with `?q=`).
- `/services` CRUD.
- `/appointments`:
  - `GET /appointments?date=YYYY-MM-DD` returns calendar data grouped by worker (JSON for frontend).
  - `POST /appointments` create; `PUT /appointments/{id}` update; `DELETE /appointments/{id}` cancel.
  - Drag/drop update uses PATCH with `starts_at`/`worker_id`/`duration_min`.
- `/reports/revenue` with date filters; `/reports/workers`.
- `/ical/{appointment}` public, signed URL that renders `text/calendar`.
- Admin per-salon settings: `/settings/sms`, `/settings/hours`.

## Blade/Tailwind UI (MVP)
- Layout: header (salon name, user menu, date picker), left sidebar (filters), main calendar.
- Calendar: columns per worker; rows per hour; appointment blocks colored by `worker.color_hex`; drag/drop to change start or move column; modal for create/edit (fields: client search/add inline with consents, service pick, duration override, notes, price_charged optional, status).
- Client form includes consent checkboxes; sends to `client_consents` on submit.
- Reports pages: simple tables with date range picker and CSV export (controller returns streamed CSV).

## Enforcement checklist
- Every model uses `HasSalon`.
- All controllers behind `TenantMiddleware` + `auth`.
- Route model binding scoped: `Route::bind('appointment', fn($id) => Appointment::where('salon_id', app('tenant.id'))->findOrFail($id));`
- Validation rules include `exists:table,id,salon_id,{tenant}` for tenant-safe relations.
- Tests: feature tests for tenant isolation (user from salon A cannot access salon B data), overlap detection, SMS job lifecycle, report sums.
