# Panduan proyek Infinite

## Desain antarmuka

- Sebelum membuat atau mengubah tampilan, baca [indeks desain](docs/design/README.md), panduan komponen yang relevan, dan stylesheet terkait di [CSS bersama](public/assets/css/root/).
- Gunakan Testing Page sebagai acuan visual. Baca view, CSS, atau JavaScript demo yang dirujuk panduan bila perlu memastikan detail implementasi.
- Grafik bulanan memakai label Jan–Des dan selalu menampilkan 12 bulan sesuai `docs/design/charts.md`. Bulan tanpa data menggunakan null.
- Untuk message informasi, berhasil, gagal, data kosong, dan memuat, ikuti `docs/design/messages.md` serta palet status bersama.
- Gunakan kembali kelas dan variabel CSS bersama yang sudah tersedia. Simpan aturan khusus halaman di stylesheet modul dan hindari menduplikasi aturan dari CSS bersama.
- Jika pekerjaan mengubah standar komponen yang dipakai bersama, perbarui panduan terkait di `docs/design` agar sesuai dengan implementasinya.
- Untuk tabel, ikuti `docs/design/table-card.md` dan gunakan `initDataTable()`. Pastikan dropdown jumlah baris tampil utuh di luar batas kartu atau area scroll, termasuk saat tabel berada di modal.
