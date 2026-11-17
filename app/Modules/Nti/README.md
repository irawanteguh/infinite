# Monitoring environment NTI

URL: `nti/nti` (akses publik tanpa login). Halaman memakai template `template/dashboard-light-blank`. Pengecualian auth dibatasi pada halaman NTI dan endpoint pembacaan sensornya di `app/Config/Filters.php`.

Konfigurasi opsional di `.env` (nilai bawaan mengikuti contoh koneksi):

```ini
nti.baseUrl = 'http://10.21.1.10'
nti.username = 'root'
nti.password = 'nti'
```

Aktifkan ekstensi PHP cURL. Web server harus dapat mengakses jaringan perangkat. Kredensial dan cookie hanya digunakan di server. Jangan simpan `.env` ke repositori.

Endpoint read-only:
- `nti/nti/snapshot`: satu login, tiga kelompok sensor; hasil per kelompok tetap tersedia bila kelompok lain gagal.
- `nti/nti/sensorinternal`, `nti/nti/sensorexternal`, `nti/nti/sensordigital`: envelope `groups` untuk masing-masing kelompok.

Dashboard membaca ulang setiap 30 detik saat tab aktif. Data gagal dibaca dikosongkan, waktu pembacaan berhasil terakhir tetap ditampilkan. Tidak ada database, pengiriman WhatsApp, riwayat permanen, atau data simulasi.

Struktur respons aktual telah diverifikasi: data.isens, data.esens, dan data.diginp. Kartu menampilkan desc sebagai nama sensor dan val sebagai nilai utama, serta mempertahankan metadata perangkat. Tidak ada interpretasi ambang suhu/kelembapan atau polaritas kontak yang diasumsikan. Gunakan panel JSON untuk memverifikasi struktur perangkat sebelum menambahkan pemetaan khusus atau alarm.
