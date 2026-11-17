# Modal dan session timeout

Acuan: app/Modules/Auth/Modal/sessiontimeout.php dan public/assets/js/root/sessiontimeout.js. [Indeks](README.md).

Modal memiliki judul, isi, serta footer tindakan. Hubungkan aria-labelledby dan aria-describedby dengan ID yang tersedia. Tombol pembuka memakai type=button dan target modal yang benar. Modal lain boleh memakai ukuran sesuai isi; lebar 560px hanya acuan modal idle, bukan ukuran wajib seluruh modal.

## Modal idle

Lebar maksimal 560px, tetap memiliki margin di layar kecil. Judul Anda masih di sini?, penjelasan lama tanpa aktivitas, countdown, tombol Logout dan Tetap masuk. Tombol utama teal. Backdrop statis dan Escape dinonaktifkan agar pilihan pengguna jelas.

Pengaturan lokal di .env; contoh juga tersedia pada file env:

~~~dotenv
session.idleTimeoutMinutes = 10
session.idleWarningSeconds = 10
~~~

Peringatan tampil setelah 10 menit tanpa aktivitas. Jika tidak memilih Tetap masuk, logout berjalan 10 detik kemudian. Jadi contoh ini bukan logout tepat pada menit kesepuluh. Nilai idle valid 1–1440 menit; peringatan 1–300 detik. Nilai tidak valid kembali ke default 10.

Pengaturan ini adalah timer idle antarmuka, berbeda dari session.expiration CodeIgniter dan bukan jaminan penegakan timeout server. Sinkronisasi aktivitas/logout antartab dan tombol Tetap masuk harus tetap berfungsi. Countdown menggunakan teks dan fokus berpindah ke tombol Tetap masuk saat modal tampil.

Jangan menyimpan atau menyalin rahasia dari .env ke dokumentasi. Setelah mengubah durasi, refresh halaman agar atribut konfigurasi baru dimuat.
