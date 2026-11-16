# Hak akses per pengguna

Jalankan `php tests/hr/access.php` dari root proyek. Pengujian memakai SQLite dalam memori; tidak membaca atau mengubah pengguna dan izin database aplikasi.

Cakupan: default welcome, pemeriksaan pengelola, batas group/organisasi, validasi modul, penolakan URL langsung, navigasi beserta parent-nya, grant/revoke tanpa login ulang, akun suspend, revisi konkurensi, audit, dan rollback saat audit gagal.

## Setup lingkungan lain

1. Jalankan `php spark hr:setup-access <username-pengelola>` menggunakan username yang telah disetujui. Perintah membuat tiga tabel dan menetapkan akun tersebut sebagai pengelola di group/organisasinya. Tidak memberikan izin modul biasa.
2. Login sebagai pengelola, lalu buka `/hr/access` untuk memberikan izin modul kepada pengguna, termasuk akun pengelola sendiri bila dibutuhkan.

Pengguna tanpa izin mendapat welcome. Pengelola mendapat welcome dan halaman pengelolaan akses. Modul biasa mencakup seluruh halaman/aksi pada pasangan package/controller yang sama. Modul aktif dan hierarkinya berasal dari `dt01_gen_modules_ms`; master pengguna/departemen tetap memakai tabel yang ada.

Pemeriksaan dilakukan oleh AuthFilter dan dipakai pula untuk navigasi. Handler impor bersama mengikuti izin modul pada package terkait karena digunakan toolbar package tersebut. Endpoint yang memang publik (misalnya formulir pasien, autentikasi, dan API dengan pengecualian filter tersendiri) tetap mengikuti konfigurasi publik yang sudah ada.

Penyimpanan menggunakan transaksi, pemeriksaan revisi, scope organisasi pada pengguna target, serta log sebelum/sesudah. Izin modul nonaktif yang tidak ada pada editor dipertahankan, tetapi tidak memberi akses ke modul nonaktif. Daftar pengelola hanya disiapkan melalui CLI, bukan melalui checkbox modul, sehingga memberi akses HR biasa tidak otomatis memberi kewenangan mengatur izin.
