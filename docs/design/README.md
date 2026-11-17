# Panduan desain aplikasi

Status: **standar sementara menggunakan modul UR**. Panduan ini mendokumentasikan pola yang sudah diterapkan; perubahan desain berikutnya mengikuti keputusan pengguna.

Dokumentasi tidak dimuat oleh browser. Mengubah Markdown tidak mengubah aplikasi. Implementasi berada di view PHP, CSS, dan JavaScript yang disebutkan pada setiap panduan.

## Daftar panduan

| Panduan | Isi | Acuan UR |
| --- | --- | --- |
| [Dasar desain](foundations.md) | Warna, tipografi, jarak, responsif | Dashboard, Tarif Positif |
| [Toolbar](toolbar.md) | Tahun, impor, perbarui, cetak | Toolbar dashboard dan Tarif Positif |
| [Card filter](filter-card.md) | Header, field, tombol, cakupan | Data E-Klaim |
| [Card ringkasan](summary-card.md) | KPI, angka, ikon, keterangan | Tarif Positif, Severity I/III, LOS |
| [Card tabel](table-card.md) | Judul, deskripsi, search, padding, footer | Daftar E-Klaim |
| [Grafik](charts.md) | Warna, sumbu, tooltip, CRR | Dashboard, Severity I/III |
| [Kotak info dan status](info-state.md) | Info, loading, error, data kosong | CRR dashboard, Casemix Index |
| [Modal dan sesi](modal-session.md) | Struktur modal dan idle timeout | Modal session timeout |
| [Pemeriksaan perubahan](review-checklist.md) | Verifikasi layout dan perilaku | Seluruh halaman UR |

## Cara menggunakan

1. Pilih halaman UR yang paling dekat dengan kebutuhan modul.
2. Gunakan komponen global yang sudah tersedia sebelum menambahkan CSS khusus halaman.
3. Pertahankan ID, nama field, endpoint, dan kriteria perhitungan yang sudah digunakan.
4. Ikuti panduan komponen dan lakukan pemeriksaan yang relevan.

Panduan ini belum berarti seluruh modul lama sudah mengikuti standar. Jangan mengubah logika bisnis hanya untuk menyamakan tampilan.
