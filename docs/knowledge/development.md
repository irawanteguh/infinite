# Pengembangan dan penelusuran masalah

## Titik awal lokal

Ikuti [AGENTS.md](../../AGENTS.md), [README proyek](../../README.md), dan kontrak runtime dari [Composer](../../composer.json). Proyek memakai document root `public/` dan memasang dependensi dengan `composer install`. Gunakan `env` sebagai template untuk konfigurasi lokal. Setup fitur memiliki command tersendiri di [app/Commands](../../app/Commands/), sehingga periksa command dan koneksi yang dipilih sebelum menjalankannya pada database yang dituju.

## Penelusuran berdasarkan gejala

| Gejala | Urutan penelusuran |
| --- | --- |
| HTTP 404 atau method salah | [Routing](routing.md), path dan kapitalisasi file/class, verb pada pemanggil JS |
| Menu hilang, redirect, atau 403 | [Autentikasi](authentication.md), katalog route, state grant pengguna, filter |
| Query kosong atau selisih laporan | [Database](database.md), scope tenant, grain dan [aturan laporan](business-rules.md), lalu kondisi data pada lingkungan yang berwenang |
| Tabel, modal, atau chart bermasalah | [Komponen bersama](shared-components.md), JS pemanggil, lalu panduan desain dan Testing Page |
| Pengiriman API gagal | [Integrasi](integrations.md), konfigurasi environment target, transport versus status HTTP, catatan bridging sesuai modul |

## Pemeriksaan yang tersedia

- `php tests/hr/access.php` memeriksa policy, penyimpanan grant, audit, dan scope dengan SQLite in-memory. [Sumber](../../tests/hr/access.php)
- `php tests/developer/module-sync.php` memeriksa katalog dan sinkronisasi menggunakan fixture dan SQLite in-memory. [Sumber](../../tests/developer/module-sync.php)
- `php tests/klaim/pending.php` memeriksa agregasi snapshot lewat fixture array. [Sumber](../../tests/klaim/pending.php)

Untuk perubahan fitur, cek sintaks PHP terkait, kontrak view/JS, serta flow HTTP sesuai ruang lingkup. SQLite di sini menguji jalur tertentu. Validasi query yang khusus MySQL perlu dilakukan pada lingkungan uji yang sesuai.

## Pemeliharaan knowledge base

Perbarui halaman modul jika controller, field, sumber data, atau workflow berubah. Perbarui halaman lintas modul ketika mekanisme bersama ikut berubah. Simpan rumus dan detail alur di satu rujukan pemilik, tambahkan tautan dari indeks, dan catat batas verifikasi. Setelah rename atau penambahan modul, periksa tautan serta daftar cakupan.

Jika pengguna meminta menambahkan atau memperbarui referensi proyek, sinkronkan [AGENTS.md proyek](../../AGENTS.md) dan dokumen terkait di `docs/` dalam satu perubahan. Gunakan halaman rujukan yang sudah ada jika cakupannya sesuai. Jika perlu membuat halaman baru, tambahkan tautannya ke indeks yang relevan.
