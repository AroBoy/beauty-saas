# Beauty SaaS - Salon Management System

Multi-tenant SaaS application for managing beauty salons.

Features:
- appointment calendar
- SMS reminders
- client management
- worker scheduling

Tech stack:
- Laravel 12
- PostgreSQL
- Redis
- Docker

## Demo

- Production: [https://studio.cudawiankiphoto.pl](https://studio.cudawiankiphoto.pl)

## Screenshots

- Dashboard: `docs/screenshots/dashboard.png`
- Architecture diagram: `docs/architecture.png`

## Architektura (skrot)

```text
Cloudflare
   |
Laravel App (Docker)
   |
PostgreSQL
   |
Redis + Queue Workers + Scheduler
```

## Funkcje

- Logowanie i panel administratora (Laravel Breeze).
- Moduly:
- pracownicy (`workers`)
- klienci (`clients`) + wyszukiwarka
- uslugi (`services`)
- kalendarz i wizyty (`appointments`) z edycja i drag&drop
- Wielotenantowosc po `salon_id` (izolacja danych salonu).
- Kolejka SMS:
- `booking_confirmation` po utworzeniu wizyty
- `reminder` przed wizyta
- Integracja z SMSAPI przez token.

## Struktura repo

- `/backend` - aplikacja Laravel
- `/docs/architecture.md` - opis architektury i modelu danych

## Wymagania

- Docker + Docker Compose
- (lokalnie) Node.js + npm lub build frontu w kontenerze
- (dla SMSAPI) aktywny token API z uprawnieniem SMS

## Szybki start lokalnie

1. Przejdz do katalogu backend:

```bash
cd backend
```

2. Skonfiguruj `.env` (na bazie `.env.example`), przykladowo:

```env
APP_URL=http://localhost
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=<UZUPELNIJ>
QUEUE_CONNECTION=database

SMS_DRIVER=smsapi
SMSAPI_TOKEN=<UZUPELNIJ>
# SMS_FROM=TwojNadawca   # tylko jesli masz zatwierdzone pole nadawcy w SMSAPI
```

3. Uruchom kontenery:

```bash
./vendor/bin/sail up -d
```

4. Migracje:

```bash
./vendor/bin/sail artisan migrate
```

5. Build frontu:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

6. Uruchom scheduler:

```bash
./vendor/bin/sail artisan schedule:work
```

## Uruchomienie produkcyjne (docker-compose.studio.yml)

W produkcji uzywamy `/backend/docker-compose.studio.yml` z serwisami:

- `app`
- `scheduler`
- `pgsql`
- `redis`

Opcjonalnie:
- `queue` - tylko jesli aplikacja bedzie miala inne zadania kolejkowane poza SMS.

Start:

```bash
cd ~/beauty-saas/backend
APP_PORT=20182 docker compose -f docker-compose.studio.yml up -d app pgsql redis scheduler
```

Sprawdzenie:

```bash
docker compose -f docker-compose.studio.yml ps
```

## Konfiguracja SMS

W `.env`:

```env
SMS_DRIVER=smsapi
SMSAPI_TOKEN=<UZUPELNIJ>
SMS_FROM=
QUEUE_CONNECTION=database
```

Wazne:

- token musi miec uprawnienie SMS w panelu SMSAPI,
- numer klienta najlepiej trzymac w formacie miedzynarodowym, np. `+48518447831`.

## Jak dziala wysylka SMS

- Komenda planisty:
- `sms:dispatch-due` - bierze `pending` i `send_at <= now()`, wykonuje `SendSmsJob` od razu i zmienia status na `sent` albo `failed`.
- Osobny worker kolejki nie jest wymagany do samej wysylki SMS.

Reczne wymuszenie:

```bash
docker compose -f docker-compose.studio.yml exec app php artisan sms:dispatch-due
```

Podglad ostatnich SMS jobow:

```bash
docker compose -f docker-compose.studio.yml exec app php artisan tinker --execute "print_r(App\Models\SmsJob::orderByDesc('id')->take(10)->get(['id','type','status','to_phone','send_at','sent_at','failure_reason'])->toArray());"
```

## HTTPS / Cloudflare Tunnel

Dla domeny produkcyjnej (`https://studio.cudawiankiphoto.pl`) aplikacja dziala za Cloudflare Tunnel.

W produkcji warto wymusic scheme `https`:

```php
if (config('app.env') === 'production') {
    URL::forceScheme('https');
}
```

To zapobiega mixed-content (zapytania `http://...` blokowane przez przegladarke).

## Typowe problemy

- `Vite manifest not found`:
- uruchom `npm install && npm run build` w kontenerze `app`.
- `relation "users" does not exist`:
- brak migracji, wykonaj `php artisan migrate --force`.
- SMS nie przychodzi mimo `Zlecono wysylke X SMS`:
- sprawdz `sms_jobs.status` i `failure_reason`,
- upewnij sie, ze `scheduler` jest uruchomiony.
- Warnings `WWWUSER/WWWGROUP`:
- dodaj do `.env`:

```env
WWWUSER=1000
WWWGROUP=1000
```

## Przydatne komendy

```bash
docker compose -f docker-compose.studio.yml ps
docker compose -f docker-compose.studio.yml logs app --tail=100
docker compose -f docker-compose.studio.yml exec app tail -n 100 storage/logs/laravel.log
docker compose -f docker-compose.studio.yml exec app php artisan config:clear
docker compose -f docker-compose.studio.yml exec app php artisan route:clear
docker compose -f docker-compose.studio.yml exec app php artisan view:clear
```

## Uwagi bezpieczenstwa

- Nie commituj prawdziwych tokenow (`SMSAPI_TOKEN`, hasla DB, APP_KEY).
- Trzymaj sekrety tylko w produkcyjnym `.env`.
