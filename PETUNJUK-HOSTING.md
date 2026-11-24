# Petunjuk hosting Laravel gratis (SnapDeploy + TiDB Cloud)

Isi paket ini cuma berkas hosting, BUKAN kode aplikasi. Salin ke akar proyek Laravel-mu:

    Dockerfile
    .dockerignore
    docker/entrypoint.sh

Syarat: proyek Laravel 12, PHP 8.3, ada package-lock.json dan `npm run build` jalan (Vite).

## 1. Tambahkan 2 perubahan kecil di kodemu

### a) bootstrap/app.php, di dalam ->withMiddleware(function (Middleware $middleware) { ... })
    $middleware->trustProxies(at: '*');

### b) app/Providers/AppServiceProvider.php
Tambah di atas class: `use Illuminate\Support\Facades\URL;`
Lalu di dalam method boot():

    if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
        URL::forceScheme('https');
        $this->app['request']->server->set('HTTPS', 'on');
    }

Tanpa ini, CSS/JS/form dibuat http:// dan diblokir browser (Mixed Content), halaman tampil polos.

### c) Data demo (opsional)
entrypoint.sh menjalankan `php artisan cargotrace:seed-if-empty` HANYA jika env SEED_DEMO=true.
Kalau kamu tidak punya perintah itu, JANGAN isi SEED_DEMO (atau ganti baris itu di
docker/entrypoint.sh dengan `php artisan db:seed --force`, tapi hanya untuk deploy pertama).

Pastikan `fakerphp/faker` boleh terpasang (Dockerfile ini tidak memakai --no-dev).

## 2. Push ke GitHub (commit lalu push sekali)

## 3. TiDB Cloud (akunmu sendiri)
Buat cluster Starter. Catat host, port (4000), username, database, lalu klik Reset Password dan simpan password-nya.

## 4. SnapDeploy (akunmu sendiri, login pakai GitHub)
1. New / Deploy Your Application, pilih repo GitHub-mu, branch main.
2. Advanced Options: Root Directory `/`, Dockerfile Path `Dockerfile`, Build Context `.`,
   Start Command `apache2-foreground`, Port `10000`.
3. Environment Variables (isi SEBELUM klik Deploy):
   APP_NAME, APP_ENV=production, APP_DEBUG=false,
   APP_KEY (jalankan `php artisan key:generate --show` di laptop),
   APP_URL=https://<alamat-container-mu>.containers.snapdeploy.app,
   DB_CONNECTION=mysql, DB_HOST, DB_PORT=4000, DB_DATABASE, DB_USERNAME, DB_PASSWORD,
   MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt,
   RUN_MIGRATIONS=true, SESSION_DRIVER=database, CACHE_STORE=database,
   QUEUE_CONNECTION=sync, SESSION_SECURE_COOKIE=true, LOG_CHANNEL=stderr
   (dan SEED_DEMO=true bila punya seeder).
   JANGAN tambah variabel PORT.
4. Redis/Memcached ditanya? Pilih "I'm using an external / hosted ..." dan kosongkan.
   Kolom AWS_* yang Required: isi `tidak-dipakai`.
5. HILANGKAN centang "Auto Deploy on Push" (kalau tidak, tiap push memakai kuota deploy).
6. Klik Deploy SEKALI. Tunggu 6-8 menit.

## 5. Aturan penting
- Kuota gratis: 5 deploy per 12 jam, 100 jam jalan per bulan. Jangan klik Retry berkali-kali.
- Container tidur setelah 15 menit tanpa akses: klik Wake Up, tunggu 1-2 menit.
- JANGAN klik tombol berbayar (Get Always-On, Sprint Pack, dll).
- Password database dan APP_KEY jangan dikirim lewat chat atau ditaruh di GitHub.
- Perubahan env: roda gigi container > Environment Variables > Save (restart, tidak memakai kuota deploy).
- Kalau error: buka Logs, cari baris merah pertama.
