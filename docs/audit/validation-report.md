# Laporan validasi

## Statis

| Pemeriksaan | Baseline | Sesudah |
| --- | --- | --- |
| PHP 7.4.33, 503 file app/tests | 502 lolos, 1 gagal karena BOM di Bahv.php | 503 lolos |
| Node.js 24.22.3, 105 file JavaScript aplikasi | 105 lolos | 105 lolos, termasuk pemeriksaan ulang Farmasi dan MDC |
| Namespace controller modul | 69 controller sesuai nama file dan namespace aktual | Ditemukan satu perbedaan dari kapitalisasi slug dispatcher, ViewMDC |
| Git diff | Working tree bersih | Diff audit lolos whitespace check. Satu PDF berubah di luar perubahan audit. |

Lingkup JavaScript adalah sumber runtime dengan ekstensi .js dan mengecualikan .min.js, library pihak ketiga, serta file JSX yang tidak dipilih oleh template. Audit juga menelusuri konfigurasi route, filter auth, izin katalog, scope model, dan penanganan token dari source. Lihat [matriks](compliance-matrix.md) untuk bukti per modul.

## Otomatis dan sintetis

| Pemeriksaan | Hasil |
| --- | --- |
| Tes Developer, sinkronisasi katalog | 30 pemeriksaan lolos. Fixture SQLite in-memory. |
| Tes HR, grant dan perubahan revisi | 40 pemeriksaan lolos. Fixture SQLite in-memory. |
| Tes Klaim, Pending Analytics | 20 pemeriksaan lolos. Data sintetis tanpa koneksi database. |
| Smoke test impor | 8 pemeriksaan lolos. Stub request, penyimpanan dan logger memeriksa respons dan hitungan setelah kegagalan tulis serta exception. Log diperiksa memakai sentinel sintetis. |
| Query, route dan template | 12 pemeriksaan lolos. Empat untuk binding group/org query Department, dua untuk deklarasi POST impor, enam untuk pemilihan satu aset per rute dalam template. |
| Grafik bulanan bersama | 5 pemeriksaan lolos dengan renderer stub. Memeriksa 12 label, posisi nilai 0 dan null, input tak termutasi, serta format seri yang dilewati helper. |

Ketiga tes repositori dijalankan sebelum dan sesudah batch. Fixture tambahan dijalankan sementara dan hanya memakai tipe request, respons dan nilai dummy.

## Browser

Chrome headless 158 memakai fixture lokal yang merender PHP view dan memuat aset repositori. Network AJAX diganti dengan Deferred sintetis. Profil sementara tidak memakai sesi aplikasi.

| Skenario | Pemeriksaan lolos | Hasil spesifik |
| --- | --- | --- |
| Desktop 1280 px | 16 | Tinggi dan tipografi banner, tabel 101 baris dan dua halaman, pencarian, karakter literal, dropdown di luar area scroll. |
| Ponsel 500 px | 16 | Tinggi dan tipografi ponsel, tanpa overflow halaman, kontrol tabel tetap bekerja. |
| Master Obat kosong 500 px | 13 | Satu inisialisasi, empty state bersama dan pesan tetap tampil setelah modal memuat ditutup. |
| Master Obat gagal 500 px | 12 | Pesan gagal tetap tampil dan inisialisasi tabel menunggu respons data. |
| Referensi MDC kosong dan gagal 500 px | 11 + 11 | Modal memuat selesai sebelum pesan ditampilkan. |

Total 79 assertion browser lolos. Assertion memantau error JavaScript pada fixture. Hasil belum mencakup render grafik aktual, warna yang dinilai manual, dropdown tabel di dalam modal, cetak, ekspor, atau semua ukuran layar.

## Belum terverifikasi di deployment

- Browser dengan sesi login dan katalog izin aktif. Fixture sengaja mengisolasi view dan JavaScript dari backend.
- Respons HTTP, file backup, workflow pengisian dan submit pengguna, integrasi eksternal dan validasi SQL pada database aplikasi aktif.
- DB schema, constraint harga, konfigurasi outbound, cakupan data nyata, dan biaya query. Tidak ada benchmark yang diklaim.
