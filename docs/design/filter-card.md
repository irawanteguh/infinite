# Kartu filter

[Indeks](README.md)

Gunakan **app-filter-card** sebagai pembungkus, **app-filter-heading** untuk judul dan deskripsi, **app-filter-fields** untuk kontrol, serta **app-filter-scope** untuk pesan cakupan atau validasi. Letakkan label di atas kontrol. Pada layar sempit, kontrol turun ke baris berikutnya.

Testing Page memakai **Jenis layanan** dengan Select2, **Dari tanggal**, **Sampai tanggal**, serta tombol **Terapkan** dan **Atur ulang**. Pilihan tahun berada di toolbar. Rentang tanggal demo mengambil seluruh bulan yang bersinggungan. Rentang terbalik memunculkan pesan merah dan mempertahankan hasil terakhir. Periode tanpa data menampilkan pesan kuning.

Untuk filter status seperti **Filter pertanyaan** di PE, kelompokkan tombol **Semua**, **Aktif**, dan **Nonaktif** dalam grup **app-filter-options** di dalam **app-filter-fields**. Gunakan **app-filter-option** untuk setiap tombol. Lebar grup mengikuti isi dan tombol dapat turun baris saat ruang sempit. Terapkan pilihan langsung, tandai tombol aktif dengan **aria-pressed**, dan tampilkan jumlah hasil di **app-filter-scope**. Di Testing Page, pilihan status langsung memperbarui jumlah pertanyaan dummy di pesan cakupan. Filter status ini bekerja secara independen dari filter tanggal dan layanan.

Saat pola ini dipakai di modul, jelaskan cakupan filter dan pertahankan nama field, pilihan, serta kriteria modul. Gunakan cakupan filter yang konsisten pada ringkasan, grafik, dan tabel.

[CSS kartu filter](../../public/assets/css/root/filter-card.css) · [Tema kontrol](../../public/assets/css/root/select-theme.css) · [Pemilih tanggal](../../public/assets/js/root/datepicker.js)
