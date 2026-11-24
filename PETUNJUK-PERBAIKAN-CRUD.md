# CargoTrace - Perbaikan Archive Shipment dan Status Location

## Yang diperbarui
- Shipment menggunakan Laravel SoftDeletes untuk Archive (bukan penghapusan permanen).
- Route web Archive ditambahkan dan dibatasi untuk Admin/Operator.
- API DELETE shipment menjalankan soft delete dan mencatat audit.
- Location mendapat kolom `is_active`, tombol nonaktifkan/aktifkan kembali, dan pilihan lokasi aktif saja pada form/API.
- File `.env` tidak disertakan. Pertahankan `.env.example` untuk konfigurasi awal.

## Instalasi lokal
1. Backup folder proyek dan database terlebih dahulu.
2. Ekstrak proyek ke folder kerja atau salin file ini ke proyek lokal. Jangan menimpa `.env` lokal yang sudah dipakai.
3. Buka terminal pada folder proyek.
4. Jalankan `php artisan migrate`.
5. Jalankan `php artisan optimize:clear`.
6. Uji Archive Shipment, Nonaktifkan/Aktifkan Location, dan pembuatan shipment dengan lokasi nonaktif.

Untuk lingkungan pengguna, gunakan PHP Laragon 8.3 atau lebih baru. Jangan menjalankan `migrate:fresh` karena akan menghapus seluruh data database.

## Catatan
Kode PHP yang diperbarui perlu diuji lagi terhadap database lokal. Migrasi belum dijalankan terhadap database pengguna. Jangan menghapus `.env` dari instalasi lokal yang sedang digunakan; file itu hanya dikecualikan dari paket distribusi.
