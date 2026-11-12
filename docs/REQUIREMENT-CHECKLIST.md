# CargoTrace Requirement Alignment Checklist

This checklist records what is present in this source package. A listed route or screen is not proof of successful end-to-end testing; run the application and verify with the database configured for the target environment.

## Application features

| SRS requirement | Current implementation |
|---|---|
| Admin / Operator / Customer roles | Role middleware and role-aware navigation/dashboard |
| Authentication | Web login, register, logout; API register/login/logout/user |
| User management | Admin can create users and update name, email, role |
| Carrier management | Admin can list, create, update, activate/deactivate carriers |
| Location management | Admin can list, create, update locations and coordinates |
| Shipment management | List, create, detail, update; customer ownership filtering |
| Shipment items | `shipment_items` migration/model; optional item entry in create/update/detail |
| Shipment lifecycle | Backend enum validates CREATED → BOOKED → PICKED_UP → IN_TRANSIT → DELIVERED |
| Tracking events | Append-only records with chronological validation, location/coordinates and creator |
| Timeline | Chronological tracking history on shipment detail |
| ETA / delay | ETA service compares latest recorded ETA with expected arrival; thresholds configurable |
| Exceptions | Create, list, update status OPEN / INVESTIGATING / RESOLVED |
| Map tracking | Leaflet + OpenStreetMap markers from origin, destination and stored tracking coordinates |
| External API | Nominatim geocoding endpoint; optional OpenWeather endpoint |
| Dashboard | Database-backed totals, status chart, delay rate, exception rate, carrier shipment chart, map and ETA list |
| Search / filter / sort / pagination | Server-side shipment query with keyword, status, carrier, origin, destination, date range, delay status, sorting and pagination |
| Notifications | In-app notifications generated for shipment creation/update, tracking and exception changes |
| Audit log | Records important shipment, tracking, exception, user, carrier and location actions |
| REST API | Authenticated JSON endpoints for shipments, tracking, exceptions, carriers, locations, dashboard, geocoding, weather and notifications |

## Still needs environment verification / delivery work

- Run migrations against a backup of the target database; this package adds a non-destructive `shipment_items` migration.
- Run `php artisan test` on a PHP environment with the required PHPUnit extensions installed.
- Test every role by logging in with separate accounts and attempt unauthorized direct URL/API access.
- Test the full lifecycle and invalid transitions, ETA thresholds, exception resolution, notifications and audit records.
- Test map tiles and Nominatim from a machine with internet access; comply with Nominatim usage policy.
- Add actual screenshots/test evidence and complete the Postman environment values.
- Configure HTTPS, production environment variables, rotate demo passwords, publish the repository and deploy the application.

No GPS hardware, live carrier integration, customs integration, payment processing or AI/ML delay prediction is included; these are outside the minimum scope stated in the SRS.
