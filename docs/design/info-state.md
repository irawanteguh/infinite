# Kotak info dan status data

Acuan: catatan CRR dashboard, Casemix Index dan Data E-Klaim. [Indeks](README.md).

## Info

Latar #f6faf9, border #dceae5, radius 12px, padding 14px 18px, gap ikon 12px. Ikon bi-info-circle berwarna #087e82, teks lembut dan tetap terbaca. Ikon rata atas untuk paragraf panjang. Di ponsel padding 12px 14px.

Gunakan role=note untuk penjelasan statis, ikon aria-hidden=true, serta judul Dasar pembacaan bila relevan. Jangan memakai alert kuning untuk informasi biasa. Warning/error tetap boleh memiliki warna semantik saat ada tindakan atau kegagalan yang perlu diperhatikan.

Implementasi info masih memakai class halaman: ur-crr-note, cm-info-note, ek-table-info, tp-method, tn-method, s1-method, s3-method, los-method. Belum ada class info global tunggal; jangan memakai class halaman lain tanpa memuat stylesheet-nya.

## Loading dan error

Gunakan status ringan dengan role=status dan aria-live=polite. Nonaktifkan tombol refresh selama request, lalu pulihkan pada semua hasil. Data E-Klaim memakai pesan loading di atas tabel, bukan kotak putih processing DataTables. Jangan menyebut gagal memuat sebagai data kosong. Error berisi langkah berikutnya seperti Muat ulang; jangan tampilkan detail SQL kepada pengguna.

## Tabel kosong

Helper dataTableEmptyState() menghasilkan ikon dokumen teal, judul Belum ada data tersedia, dan petunjuk Ubah periode atau filter untuk melihat data yang tersedia. Pencarian tanpa hasil memakai judul Tidak ada data yang sesuai dan petunjuk mengubah kata pencarian/filter.

State loading/error mempertahankan pesan operasional. Jangan menambahkan nilai contoh atau angka rekaan untuk mengisi tabel kosong. Gunakan atribut hidden dengan benar; style display:flex tidak boleh membuat elemen hidden muncul kembali.
