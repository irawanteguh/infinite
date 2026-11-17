# Toolbar

Acuan: app/Modules/Ur/Toolbar/dashboard.php dan tarifpositif.php. [Indeks](README.md).

## Susunan

Filter tahun pulang, tombol Impor E-Klaim, lalu aksi Perbarui dan Cetak ringkasan. Gunakan label Indonesia yang singkat dan ikon Bootstrap Icons dengan aria-hidden untuk ikon dekoratif.

| Elemen | Class yang tersedia |
| --- | --- |
| Kelompok periode dan impor | ur-toolbar |
| Label dan select tahun | ur-toolbar-period |
| Tombol impor | btn btn-sm ur-toolbar-import |
| Kelompok aksi | module-toolbar-actions |

Sumber: public/assets/css/ur/dashboard.css, compact-layout.css, button-theme.css, dan select-theme.css. Class ur-toolbar memerlukan stylesheet UR; class ini belum menjadi komponen global untuk semua modul.

Select tahun tetap menggunakan name/id selectperiode jika script modul bergantung padanya. Gunakan label for yang cocok dengan ID; placeholder Pilih tahun. Hindari menambahkan class select2-hidden-accessible secara manual; class itu dikelola Select2.

Gunakan button type=button untuk membuka modal atau memuat ulang. Atribut target modal harus cocok dengan ID modal impor yang tersedia. Saat memuat, nonaktifkan Perbarui dan aktifkan kembali setelah permintaan selesai.

Pada layar kecil, kelompok aksi boleh berpindah baris. Jangan memaksakan seluruh tombol dalam satu baris hingga keluar layar.
