# Pesan dan status

[Indeks](README.md)

Gunakan bagian **Status dan modal** pada Developer → Testing Page sebagai acuan message. Pesan menjelaskan hasil tindakan, kondisi data, atau langkah yang perlu dilakukan pengguna.

## Warna dan isi

| Kondisi | Palet | Ikon |
| --- | --- | --- |
| Informasi | Teal soft | `bi-info-circle` |
| Berhasil | Teal soft | `bi-check-circle` |
| Menunggu atau data kosong | Kuning soft | `bi-exclamation-circle` |
| Gagal atau validasi | Merah soft | `bi-exclamation-triangle` |
| Memuat | Kuning soft | Spinner Bootstrap |

Gunakan pasangan variabel `--app-status-teal-bg` dan `--app-status-teal-text`, atau pasangan yellow dan red dari CSS tema bersama. Ikon mengikuti warna teks. Sertakan teks agar arti pesan tetap jelas.

Gunakan kelas bersama `alert alert-info` untuk informasi, `alert alert-success` untuk berhasil, `alert alert-warning` untuk data kosong atau menunggu, dan `alert alert-danger` untuk gagal memuat. Palet kelas ini ditetapkan di `theme-palette.css` mengikuti Testing Page. Pesan gagal memuat menggunakan judul **Gagal memuat** dan ikon `bi-exclamation-circle`, diikuti rincian pesan.

## Badge status

Badge memakai palet yang sama dengan pesan. Definisi bersama ada di `theme-palette.css` dan berlaku pada badge di halaman, tabel, serta modal.

| Arti | Kelas yang digunakan | Latar | Teks |
| --- | --- | --- | --- |
| Informasi, kategori, aktif, berhasil | `badge badge-light-primary`, `badge badge-light-info`, atau `badge badge-light-success` | #EAF6F4 | #087E82 |
| Menunggu, perhatian, belum lengkap | `badge badge-light-warning` | #FFF4D6 | #805800 |
| Gagal, ditolak, nonaktif | `badge badge-light-danger` | #FDE9E9 | #A63C45 |

Gunakan label yang menjelaskan arti status. Ikon mengikuti warna teks. Pilih kelas berdasarkan arti data yang sudah ditetapkan modul. Kelas solid `badge-primary/info/success/warning/danger` dan `bg-primary/info/success/warning/danger` pada elemen `.badge` juga diarahkan ke palet ini untuk kompatibilitas halaman lama. Untuk implementasi baru, gunakan varian `badge-light-*`. Warna domain khusus seperti tingkat matriks risiko mengikuti definisi domain yang terverifikasi. Simpan aturan warna status pada stylesheet bersama.

## Penempatan

Konfirmasi tindakan di dalam aplikasi menggunakan SweetAlert, termasuk penghapusan dan pengabaian perubahan. Gunakan judul singkat, keterangan dampak tindakan, ikon `question` teal, `showCancelButton: true`, `buttonsStyling: false`, tombol aksi `btn btn-primary`, dan tombol Cancel `btn btn-light ms-3`. Teks tombol aksi menjelaskan tindakannya, misalnya **Hapus environment**. Jalankan tindakan hanya jika `result.isConfirmed` bernilai true. Peringatan saat menutup atau memuat ulang tab tetap memakai mekanisme `beforeunload` bawaan browser.

Ikon SweetAlert untuk konfirmasi (`question`) dan informasi (`info`) memakai teal #087E82 pada garis lingkaran dan simbol di dalamnya. Atur melalui CSS tema bersama `theme-palette.css` agar konsisten di semua modul, termasuk konfirmasi impor E-Klaim. Tombol konfirmasi memakai `btn btn-primary` sesuai tema tombol bersama.

- Letakkan hasil tindakan dekat komponen terkait atau di atas isi halaman setelah formulir disimpan.
- Gunakan alert Bootstrap dengan padding dan sudut membulat dari tema. Judul singkat boleh diikuti rincian jika diperlukan.
- Gunakan `role="status"` untuk hasil berhasil dan informasi yang diperbarui. Gunakan `role="alert"` untuk kegagalan yang perlu segera diketahui. Ikon dekoratif memakai `aria-hidden="true"`.
- Pesan gagal menyebut tindakan berikutnya yang dapat dilakukan pengguna. Tampilkan rincian teknis pada log server.
- Pesan data kosong menjelaskan cakupan data dan langkah berikutnya jika tersedia.
- Untuk proses yang sedang berjalan, ikuti [Modal progres](report-progress.md). Untuk pesan cakupan filter, gunakan `app-filter-scope` sesuai [Kartu filter](filter-card.md).

## Contoh teks

| Kondisi | Pesan |
| --- | --- |
| Berhasil | Tabel aktivitas pengguna siap. |
| Informasi | Riwayat aktivitas menampilkan 30 hari terakhir. |
| Data kosong | Belum ada aktivitas pada tanggal yang dipilih. |
| Gagal | Log belum dapat dimuat. Silakan muat ulang halaman. |
| Memuat | Memuat riwayat aktivitas… |

Atribut `data-testing-tone` pada demo mengikuti stylesheet Testing Page. Pada halaman modul, gunakan variabel palet bersama dalam stylesheet modul, atau kelas pesan bersama yang sudah tersedia.

[Palet bersama](../../public/assets/css/root/theme-palette.css) · [View Testing Page](../../app/Modules/Developer/Views/v_testingpage.php) · [CSS demo](../../public/assets/css/developer/testingpage.css)

Saat memeriksa modul, telusuri juga badge dari JavaScript dan aturan CSS lokal. Badge kustom yang sudah memakai variabel palet bersama dapat dipertahankan. Kondisi belum diisi atau status belum tersedia memakai warning, dan status nonaktif memakai danger.