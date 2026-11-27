# Dasar tampilan

[Indeks](README.md)

## Layout dan tipografi

Gunakan Poppins dari template, grid Bootstrap/Metronic, dan jarak dari layout bersama. Letakkan filter sebelum ringkasan dan grafik. Kartu grafik berpasangan pada layar lebar dan bertumpuk pada layar sempit. Judul menyebut fungsi komponen, misalnya **Grafik garis** atau **Tabel data**. Tambahkan deskripsi bila diperlukan untuk menjelaskan satuan atau cakupan.

## Input, select, dan button

Gunakan bagian **Input, select, dan button** pada [Testing Page](http://localhost/infinite/developer/testingpage#testing-controls) sebagai referensi visual ukuran kontrol.

Input teks, angka, tanggal, pencarian, email, URL, telepon, dan password memakai padding standar 6px vertikal dan 10px horizontal, dengan tinggi minimum 34px melalui `typography.css`. Ruang horizontal tambahan untuk ikon atau tombol di dalam input ditetapkan pada stylesheet modul. Pencarian tabel mengikuti pembungkus `app-table-search`. Checkbox, radio, file, warna, range, dan input tersembunyi mengikuti ukuran komponen masing-masing.

Select native dan Select2 single memakai padding 6px vertikal, 10px kiri, dan 32px kanan untuk panah, dengan tinggi minimum 34px. Tombol aksi berlabel memakai padding 6px vertikal dan 10px horizontal serta tinggi minimum 34px. Tombol ikon, tombol tutup modal, dan item menu mengikuti ukuran komponen masing-masing. Aturan bersama tersedia di `typography.css`.

## Palet

| Pemakaian | Latar | Teks dan ikon |
| --- | --- | --- |
| Info dan berhasil | #EAF6F4 | #087E82 |
| Menunggu, memuat, dan data kosong | #FFF4D6 | #805800 |
| Gagal dan kesalahan validasi | #FDE9E9 | #A63C45 |

Tombol primer memakai teal #087E82 dengan teks putih dan hover #066B6E. Teks utama pada contoh kartu memakai #234A54. Sertakan label atau ikon yang dapat dikenali bersama warna status.

Badge status memakai palet yang sama melalui kelas `badge-light-*`. Pemetaan arti status dan kompatibilitas kelas lama ada di [referensi badge](messages.md#badge-status).

## Navigasi samping dan navbar

Gunakan latar teal soft serta teks, ikon, dan bullet teal saat menu aside atau navbar di-hover, mendapat fokus keyboard, atau aktif. Induk submenu yang terbuka memakai warna yang sama. Label navbar dengan parent H ditampilkan dalam huruf kapital. [CSS navigasi](../../public/assets/css/root/navigation-menu.css)

## Banner geometris

Contoh tersedia di bagian atas Testing Page. Banner contoh memakai sudut membulat 20px, padding 24px pada desktop dan 20px pada ponsel, serta judul dan deskripsi singkat.

Variasi pola tersedia pada Testing Page dengan lapisan gradien teal gelap untuk menjaga keterbacaan judul dan deskripsi.

Pertahankan gradasi teal banner saat mengganti atau meningkatkan ketajaman aset pola. Gunakan lapisan berikut sesuai contoh Testing Page.

```css
background-image:
    linear-gradient(100deg, #102c37 5%, rgb(18 56 63 / 95%) 38%, rgb(8 126 130 / 55%) 100%),
    var(--testing-banner-pattern);
```

Perbaikan ketajaman pola dilakukan pada kualitas aset sumber dengan mempertahankan warna, arah, dan opasitas gradasi ini.

| Variasi | Kelas contoh | Aset |
| --- | --- | --- |
| Pola abstrak | `testing-banner-chaotic` | `public/assets/media/banners/chaotic.jpg` |
| Geometri segitiga | `testing-banner-triangles` | `public/assets/media/banners/triangles.jpg` |
| Pola persegi | `testing-banner-squares` | `public/assets/media/banners/squares.jpg` |
| Ornamen floral | `testing-banner-floral` | `public/assets/media/banners/floral.jpg` |
| Gelombang organik | `testing-banner-waves` | `public/assets/media/banners/waves.jpg` |
| Geometri diagonal | `testing-banner-diagonal` | `public/assets/media/banners/diagonal.jpg` |

Gunakan gambar sebagai latar dekoratif dengan teks HTML di atasnya. Terapkan variabel `--app-banner-heading` dan `--app-banner-copy` untuk warna teks. Atur pemotongan pola melalui background-position dan background-size agar teks tetap terbaca pada desktop dan ponsel. Implementasi contoh berada di `public/assets/css/developer/testingpage.css`. Untuk halaman lain, gunakan komponen banner bersama dan simpan pengaturan pola khusus halaman di stylesheet modul.

## Banner Betawi

Banner dekoratif modul memakai kelas **app-geometric-banner** dari [CSS banner bersama](../../public/assets/css/root/geometric-banner.css). Pola standar memakai [aset Betawi](../../public/assets/media/banners/faq-betawi-scatter.png) dengan ornamen garis kecil ondel-ondel, Monas, Abang None, dan kawung tersebar acak. Variasi jarak, ukuran, serta rotasi ringan menjaga tampilan modern.

Pertahankan latar teal gelap, aksen garis teal–emas, serta overlay lebih gelap di sisi teks. Ornamennya tetap terlihat pada sisi kanan. Gunakan variabel `--app-banner-heading`, `--app-banner-copy`, dan `--app-banner-accent` untuk teks dan ikon. Pengaturan pola, overlay, dan pemotongan dimiliki CSS bersama. Stylesheet modul menangani dimensi dan tata letak konten.

Standar berlaku pada banner Welcome, FAQ, Operasional Sistem, login, landing, dan panel sambutan survei yang memakai kelas bersama. Contoh variasi pola di Testing Page tetap menjadi demonstrasi alternatif. Periksa keterbacaan dan pemotongan gambar pada desktop dan ponsel ketika menambahkan banner.

### Posisi judul dan deskripsi banner

Bungkus judul dan deskripsi dengan `app-banner-heading` di awal banner header modul. Tempatkan pembungkus di kiri atas area isi, dengan teks rata kiri dan lebar maksimum 680 px. Pertahankan padding banner responsif. Pada ponsel, teks tetap rata kiri dan membungkus mengikuti lebar yang tersedia. Kontrol tambahan ditempatkan sesudah pembungkus. Judul memakai `--app-banner-heading` dan deskripsi memakai `--app-banner-copy` dari CSS bersama.

### Tipografi banner modul

| Lebar layar | Judul | Deskripsi |
| --- | --- | --- |
| Desktop dan tablet, mulai 768 px | 32 px, tebal 700, line-height 1.3 | 16 px, line-height 1.7 |
| Ponsel, di bawah 768 px | 26 px, tebal 700, line-height 1.3 | 14 px, line-height 1.7 |

Ukuran diterapkan pada judul dan deskripsi di dalam `app-banner-heading` melalui CSS banner bersama. Aturan ini mendapat prioritas atas kelas ukuran utilitas template dan tipografi umum. Pertahankan posisi kiri atas, rata kiri, serta tinggi yang mengikuti isi saat teks membungkus.

### Tinggi banner modul

Banner header halaman modul memakai `app-geometric-banner app-module-banner`.

| Lebar layar | Tinggi minimum termasuk padding |
| --- | --- |
| Desktop dan tablet, mulai 768 px | 180 px |
| Ponsel, di bawah 768 px | 160 px |

Gunakan `min-height` dari CSS bersama melalui variabel `--app-module-banner-min-height`. Tinggi mengikuti isi ketika judul, deskripsi, kontrol, atau panel tambahan membutuhkan ruang. Pertahankan padding responsif dan hindari tinggi tetap yang memotong isi. Ukuran ini diterapkan pada Welcome, FAQ, Operasional Sistem, Encounter, dan Data Kelahiran. Landing, panel login, dan panel sambutan survei mengikuti dimensi layout masing-masing. Untuk banner header modul baru, tambahkan kedua kelas tersebut dan periksa pembungkusan teks pada ponsel.

## Kartu ringkasan

Susun label dan ikon di bagian atas kartu. Letakkan angka utama yang tebal dan satuannya pada baris berikutnya. Kartu total memakai latar #F6FBFA dan angka teal. Kartu rincian memakai latar putih dan wadah ikon teal soft. Perbarui semua kartu sesuai cakupan filter.

## Dasar pembacaan dan modal

Panel **Dasar pembacaan** memakai latar teal muda, aksen di atas, dua kolom pada layar yang cukup lebar, dan catatan di bawah garis pemisah. Gunakan panel untuk menjelaskan arti angka, sumber, atau cara membaca grafik.

Modal berpusat di layar dan memiliki judul, isi singkat, serta tombol tutup. Modal detail mengambil data dari baris yang dipilih. Modal ringkasan mengikuti filter yang telah diterapkan.

[Tipografi bersama](../../public/assets/css/root/typography.css) · [Layout bersama](../../public/assets/css/root/compact-layout.css) · [Palet bersama](../../public/assets/css/root/theme-palette.css) · [Tema tombol](../../public/assets/css/root/button-theme.css) · [CSS contoh](../../public/assets/css/developer/testingpage.css)
