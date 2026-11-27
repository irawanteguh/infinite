
# Panduan Proyek Infinite

## Prinsip Utama

- Ikuti arsitektur, konvensi, dan pola implementasi proyek.
- Kerjakan perubahan sesuai ruang lingkup permintaan.
- Gunakan kembali komponen dan fungsi yang sudah tersedia.
- Hindari refactoring yang tidak berkaitan dengan tugas.
- Jangan mengubah aturan bisnis tanpa persetujuan.
- Instruksi khusus proyek melengkapi global AGENTS.md.

## Context Management

- Identifikasi modul, route, controller, model, view,
  dan aset yang berkaitan dengan tugas.
- Gunakan pencarian terarah sebelum membaca file.
- Hindari scanning seluruh repository tanpa kebutuhan.
- Jangan membaca ulang file yang tidak berubah jika
  informasi sebelumnya masih relevan.
- Abaikan vendor, node_modules, cache, logs, backup,
  database dump, dan generated assets kecuali diperlukan.
- Baca dokumentasi dan referensi secara selektif.
- Jangan memuat seluruh dokumentasi desain sekaligus.
- Batasi output command pada informasi yang relevan.

## Knowledge Base

- Mulai dari [indeks Knowledge Base](docs/knowledge/README.md) dan
  baca dokumen yang relevan dengan tugas.
- Verifikasi perilaku pada kode sumber terkini sebelum implementasi.
- Perbarui dokumentasi terdampak ketika arsitektur, alur kerja, atau
  aturan bisnis terverifikasi berubah.

## Pemeliharaan FAQ & Documentation

- Halaman bantuan pengguna berada di `developer/faq`.
  Controller berada di `app/Modules/Developer/Controllers/Faq.php`
  dan view di `app/Modules/Developer/Views/v_faq.php`.
  Konten modul berada di `app/Modules/Developer/Config/HelpGuides.php`.
  Pencarian dan gaya halaman berada di `public/assets/js/developer/faq.js`
  dan `public/assets/css/developer/faq.css`.
- Setiap agent yang menambah atau mengubah fitur modul wajib
  memperbarui panduan modul dan FAQ yang terdampak pada halaman
  tersebut dalam pekerjaan yang sama, tanpa menunggu permintaan ulang.
- Dokumentasikan tujuan fitur, cara membuka dan menggunakan,
  filter/periode, persyaratan akses, serta perubahan perilaku yang
  relevan bagi pengguna berdasarkan implementasi yang terverifikasi.
- Untuk modul baru, tambahkan panduan pengguna yang sesuai.
  Untuk fitur yang berubah atau dihapus, sesuaikan panduan lama
  agar tetap mencerminkan perilaku aplikasi saat ini.
- Gunakan bahasa Indonesia, komponen desain bersama, dan uraian
  singkat. Hindari duplikasi panduan serta data pasien dan rahasia.
- Sebelum menyelesaikan pekerjaan, periksa kesesuaian panduan
  dengan kode dan validasi sintaks file FAQ yang diubah.
- Perbarui juga dokumen terkait di `docs/knowledge/` untuk perubahan
  arsitektur, workflow, atau aturan bisnis terverifikasi.

## Agent Skills

Gunakan skill sesuai jenis pekerjaan:

- `teguh-dev-workflow`: development, debugging,
  UI/UX, API, database, integrasi, dan deployment.
- `teguh-data-analysis`: analisis data, statistik,
  KPI, indikator, rekonsiliasi, dan pelaporan.

Gunakan keduanya jika pekerjaan mencakup development
dan analisis data.

Baca hanya referensi skill yang relevan dengan tugas.
Jangan memuat seluruh references secara otomatis.

## Arsitektur Aplikasi

- Infinite menggunakan CodeIgniter 4.
- Verifikasi struktur modul dan versi dependensi aktual.
- Ikuti pola controller, model, view, route, dan service
  yang sudah digunakan pada modul terkait.
- Gunakan kembali helper, library, dan shared utilities.
- Pertahankan kontrak API dan format response.
- Pertahankan autentikasi, otorisasi, dan session.
- Jangan memperkenalkan framework atau dependency baru
  jika komponen yang tersedia sudah mencukupi.

## Database dan Data Integrity

- Verifikasi nama tabel, kolom, dan database connection.
- Pertahankan relasi dan aturan bisnis yang sudah ada.
- Pertahankan isolasi group, organization, dan department
  sesuai mekanisme otorisasi proyek.
- Gunakan parameter binding untuk input dinamis.
- Hindari N+1 query dan pengambilan data berlebihan.
- Lakukan agregasi dataset besar pada database.
- Pertahankan perbedaan antara null dan nilai nol.
- Jangan membuat ulang kolom yang sengaja dihapus.
- Jangan menjalankan perubahan database destruktif
  tanpa persetujuan.

## Desain Antarmuka

- Banner dekoratif modul memakai kelas `app-geometric-banner`
  dan standar [banner Betawi](docs/design/foundations.md#banner-betawi).
- Banner header modul juga memakai `app-module-banner`, dengan
  tinggi minimum 180 px mulai lebar 768 px dan 160 px pada ponsel.
  Tinggi bertambah mengikuti isi. Ikuti referensi tinggi banner di
  `docs/design/foundations.md`; gunakan definisi CSS bersama.
- Gunakan ornamen garis kecil ondel-ondel, Monas, Abang None, dan
  kawung yang tersebar acak dengan rotasi ringan serta palet teal–emas.
- Gunakan aset dan overlay dari CSS bersama
  `public/assets/css/root/geometric-banner.css`; hindari override pola
  di stylesheet modul. Verifikasi keterbacaan dan pemotongan pada ponsel.

- Sebelum membuat atau mengubah tampilan, baca
  `docs/design/README.md`, panduan komponen yang relevan,
  dan stylesheet terkait di `public/assets/css/root/`.
- Gunakan Testing Page sebagai acuan visual.
- Baca view, CSS, atau JavaScript demo yang dirujuk
  panduan bila diperlukan.
- Gunakan kembali kelas dan variabel CSS bersama.
- Simpan aturan khusus halaman di stylesheet modul.
- Hindari menduplikasi aturan dari CSS bersama.
- Pertahankan konsistensi font, warna, spacing,
  layout, dan visual hierarchy.
- Gunakan animasi secara proporsional dan tidak berlebihan.
- Pertahankan responsive layout dan aksesibilitas.
- Jika standar komponen bersama berubah, perbarui
  panduan terkait di `docs/design`.

## Tabel

- Ikuti `docs/design/table-card.md`.
- Gunakan `initDataTable()`.
- Pastikan dropdown jumlah baris tampil utuh di luar
  batas kartu atau area scroll, termasuk dalam modal.
- Pertahankan stabilitas lebar halaman dan tabel.
- Hindari inisialisasi DataTables berulang.
- Gunakan server-side processing jika diperlukan
  untuk dataset besar.

## Grafik dan Dashboard

- Ikuti `docs/design/charts.md`.
- Grafik bulanan memakai label Jan–Des.
- Selalu tampilkan 12 bulan untuk grafik bulanan.
- Bulan tanpa data menggunakan null.
- Pertahankan definisi metrik dan periode pelaporan.
- Jangan mengubah rumus indikator tanpa persetujuan.
- Gunakan library grafik yang sudah tersedia.
- Hindari duplikasi instance grafik.

## Messages dan Feedback

- Ikuti `docs/design/messages.md`.
- Gunakan palet status bersama.
- Pertahankan standar message informasi, berhasil,
  gagal, data kosong, dan memuat.
- Gunakan komponen notifikasi yang sudah tersedia.
- Cegah pengiriman form berulang.

## Keamanan Data

- Pertahankan mekanisme autentikasi dan otorisasi.
- Validasi input pada sisi server.
- Pertahankan CSRF protection.
- Jangan mengekspos credentials, token, atau secrets.
- Lindungi informasi pasien dan data rumah sakit.
- Jangan menampilkan data sensitif pada log atau
  output pengujian.

## Validasi

- Lakukan pemeriksaan syntax pada file yang diubah.
- Jalankan pengujian relevan jika tersedia.
- Periksa route, permission, dan data isolation.
- Periksa kemungkinan regression pada modul terkait.
- Review git diff sebelum menyelesaikan pekerjaan.
- Bedakan static validation dari live verification.
- Jangan mengklaim pengujian berhasil tanpa bukti.

## Pelaporan Hasil

Laporkan secara ringkas:

- Perubahan yang dilakukan.
- File yang diubah.
- Hasil validasi.
- Kendala atau risiko yang masih ada.
