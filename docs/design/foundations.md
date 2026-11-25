# Dasar tampilan

[Indeks](README.md)

## Layout dan tipografi

Gunakan Poppins dari template, grid Bootstrap/Metronic, dan jarak dari layout bersama. Letakkan filter sebelum ringkasan dan grafik. Kartu grafik berpasangan pada layar lebar dan bertumpuk pada layar sempit. Judul menyebut fungsi komponen, misalnya **Grafik garis** atau **Tabel data**. Tambahkan deskripsi bila diperlukan untuk menjelaskan satuan atau cakupan.

## Palet

| Pemakaian | Latar | Teks dan ikon |
| --- | --- | --- |
| Info dan berhasil | #EAF6F4 | #087E82 |
| Menunggu, memuat, dan data kosong | #FFF4D6 | #805800 |
| Gagal dan kesalahan validasi | #FDE9E9 | #A63C45 |

Tombol primer memakai teal #087E82 dengan teks putih dan hover #066B6E. Teks utama pada contoh kartu memakai #234A54. Sertakan label atau ikon yang dapat dikenali bersama warna status.

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

Gunakan kelas **app-geometric-banner** untuk banner bergaya Welcome Page. Pola segitiga dan bidang bersudut berada di sisi kanan agar area teks tetap terbaca. Gunakan teal gelap yang sama pada banner sambutan dan gunakan variabel warna judul, teks, serta ikon yang disediakan CSS banner. [CSS banner bersama](../../public/assets/css/root/geometric-banner.css)

## Kartu ringkasan

Susun label dan ikon di bagian atas kartu. Letakkan angka utama yang tebal dan satuannya pada baris berikutnya. Kartu total memakai latar #F6FBFA dan angka teal. Kartu rincian memakai latar putih dan wadah ikon teal soft. Perbarui semua kartu sesuai cakupan filter.

## Dasar pembacaan dan modal

Panel **Dasar pembacaan** memakai latar teal muda, aksen di atas, dua kolom pada layar yang cukup lebar, dan catatan di bawah garis pemisah. Gunakan panel untuk menjelaskan arti angka, sumber, atau cara membaca grafik.

Modal berpusat di layar dan memiliki judul, isi singkat, serta tombol tutup. Modal detail mengambil data dari baris yang dipilih. Modal ringkasan mengikuti filter yang telah diterapkan.

[Tipografi bersama](../../public/assets/css/root/typography.css) · [Layout bersama](../../public/assets/css/root/compact-layout.css) · [Palet bersama](../../public/assets/css/root/theme-palette.css) · [Tema tombol](../../public/assets/css/root/button-theme.css) · [CSS contoh](../../public/assets/css/developer/testingpage.css)
