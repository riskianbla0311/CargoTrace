# CargoTrace — Global Shipment Visibility & Tracking Platform

A Laravel 13 + PHP 8.3 application starter aligned to the CargoTrace UAS brief.

## Stack
- Laravel 13, PHP 8.3+
- MySQL 8+ with Eloquent, migrations, seeders
- Blade views, responsive CSS, vanilla JavaScript
- Laravel Sanctum for REST API bearer tokens
- Leaflet + OpenStreetMap for shipment route map
- Chart.js for dashboard visualization
- Laravel HTTP Client integration with OpenStreetMap Nominatim geocoding and optional OpenWeather

## Features included
- Three roles: admin, operator, customer; server-side role checks
- Web login/logout and API register/login/logout/user
- Shipment CRUD create/read/update/list, cargo item details, search, multi-filter, sorting and server-side pagination
- Shipment lifecycle validation: CREATED → BOOKED → PICKED_UP → IN_TRANSIT → DELIVERED
- Append-only tracking events with chronological validation
- ETA/delay assessment (thresholds configurable in `.env`: 6h LOW, 24h MEDIUM)
- Exception types and severity/status management with customer notifications and audit history
- Carrier/location administration
- Customer ownership restriction
- Dashboard metrics and status chart
- Map origin/destination/tracking points with OpenStreetMap attribution
- Audit logs for shipment, tracking, exception, user, carrier, and location changes
- Automated feature-test examples

## Setup on Laragon (Windows)
1. Extract this project into `C:\\laragon\\www\\CargoTrace-Complete` (or a folder you choose).
2. Copy `.env.example` to `.env`.
3. Create a MySQL database named `cargotrace` using Laragon / phpMyAdmin.
4. Configure DB settings in `.env` (default Laragon root password is often blank; use your own local settings).
5. Open terminal in project folder and run:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   ```
   If Sanctum is not present after composer install, run `composer require laravel/sanctum`.
6. Open `http://127.0.0.1:8000`.

## Demo accounts
- Admin: `admin@cargotrace.test` / `password`
- Operator: `operator@cargotrace.test` / `password`
- Customer: `customer@cargotrace.test` / `password`

Change demo passwords before deployment. Never publish `.env`.

## REST API
Base URL: `http://127.0.0.1:8000/api`. Send `Accept: application/json`.
Protected routes need `Authorization: Bearer <token>`.

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/register` | Customer registration |
| POST | `/login` | Get Sanctum token |
| POST | `/logout` | Revoke current token |
| GET | `/user` | Current user |
| GET, POST | `/shipments` | List/create shipments |
| GET, PUT/PATCH, DELETE | `/shipments/{id}` | Shipment details/update/delete |
| GET, POST | `/shipments/{id}/tracking` | Tracking history/add event |
| GET, POST | `/shipments/{id}/exceptions` | List/create exception |
| PATCH | `/exceptions/{id}` | Update exception |
| GET, POST | `/carriers` | List/create carriers |
| GET, POST | `/locations` | List/create locations |
| GET | `/dashboard/summary` | Dashboard metrics |
| GET | `/geocode?q=Jakarta+Port` | Search address/location coordinates using Nominatim |\n| GET | `/weather?latitude=-6.2&longitude=106.8` | Optional weather context |
| GET | `/notifications` | Current user's notifications |
| PATCH | `/notifications/{id}/read` | Mark notification as read |

## API examples
Login:
```json
{"email":"admin@cargotrace.test","password":"password"}
```
Create shipment:
```json
{"shipment_number":"CT-2026-0002","customer_id":3,"carrier_id":1,"origin_id":1,"destination_id":2,"cargo_type":"General Cargo","weight_kg":120,"volume_m3":1.5,"expected_departure_at":"2026-11-12 09:00:00","expected_arrival_at":"2026-11-15 18:00:00"}
```

## External geocoding and weather APIs\nGeocoding uses OpenStreetMap Nominatim through Laravel HTTP Client and does not require an API key. Requests use a timeout and return a safe unavailable response if the provider fails. Follow the Nominatim usage policy and avoid bulk requests.\n\n
Set `OPENWEATHER_API_KEY` in `.env` to enable the included `WeatherService`. A missing key or provider failure returns a safe unavailable response. Weather data is contextual information, not proof that a shipment is delayed.

## Notes / remaining production hardening
This is a comprehensive academic MVP foundation, not a carrier-connected production logistics system. Before public deployment: rotate demo credentials, configure HTTPS, add rate limits, review authorization policies and API tests, implement email/in-app notification delivery workflows, and add Postman collection/environment values. Map tiles require internet access and OpenStreetMap attribution.


The included `.env.example` uses MySQL. The ZIP intentionally excludes `.env`, `vendor/`, `node_modules/`, duplicate backup folders, and generated cache files. Run `composer install` in Laragon before starting the app. Composer will resolve the dependencies, including Sanctum, from `composer.json` when you run `composer install`.
