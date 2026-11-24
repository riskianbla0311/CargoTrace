# CargoTrace – Multimodal, International, and Tracking Updates

## Shipment structure

A shipment keeps one shipment number and one customer record. Its route can contain multiple ordered transport legs (`shipment_legs`). Each leg stores its own transport mode (`LAND`, `SEA`, or `AIR`), carrier, origin, destination, planned/actual schedule, status, and notes.

The first leg is created automatically when an operator/admin creates a shipment. Additional legs are added from the shipment detail page. Example: LAND (warehouse to port) → SEA (port to port) → LAND (port to recipient).

## International shipment fields

- `shipment_type`: `DOMESTIC` or `INTERNATIONAL`
- `hs_code`: required for international shipments
- `incoterms`: optional trade term
- `customs_status`: `PENDING`, `IN_REVIEW`, `CLEARED`, `HELD`, or `NOT_REQUIRED`
- `customs_reference`: optional customs/document reference

Origin and destination locations retain their country information in the existing `locations` table.

## Tracking and ETA

Tracking updates are appended as historical events; they do not overwrite previous events. The tracking service validates event chronology and shipment lifecycle transitions, updates the shipment status, and creates customer notifications and audit records. ETA assessment uses the latest tracking event's estimated arrival against the shipment's expected arrival and the configured delay thresholds.

This implementation reflects updates entered by an operator or admin and stored by the application. It does not claim physical GPS telemetry or direct carrier/port/customs integration. Coordinates can be supplied with tracking events for map display.

## Safe update

Back up the project and database before updating an existing installation. The new migration only adds nullable/defaulted shipment metadata columns. Run `php artisan migrate` after configuring `.env`; do not run `migrate:fresh` on a database containing data.

The distributable project contains `.env.example`, not a machine-specific `.env`. Copy `.env.example` to `.env`, configure database/API settings, and generate an application key only when setting up a fresh local copy.

## Pembaruan tracking otomatis (tanpa perangkat GPS)

Halaman detail shipment kini memeriksa endpoint `GET /shipments/{shipment}/tracking/live` setiap 15 detik. Endpoint ini hanya membaca tracking event yang sudah disimpan operator, memeriksa hak akses customer agar hanya dapat melihat shipment miliknya, dan mengembalikan status terbaru, ETA, serta maksimal 30 event terbaru. Timeline, status, dan ETA pada halaman detail diperbarui tanpa pengguna menekan tombol refresh.

**Batasan yang perlu dipahami:** fitur ini adalah pembaruan otomatis berbasis data yang dimasukkan operator, bukan pelacakan GPS kendaraan secara langsung. Titik peta berubah ketika operator menyimpan tracking event baru yang memiliki koordinat. Tidak ada lokasi kendaraan yang dibuat-buat. Untuk GPS sungguhan diperlukan perangkat GPS atau API penyedia armada.

Polling menggunakan sesi login browser dan rute web terlindungi. Jika koneksi terputus, halaman menampilkan pesan sementara dan mencoba kembali pada interval berikutnya.
