# Kartu tabel

[Indeks](README.md)

Gunakan **app-table-card**, **app-table-heading**, **app-table-title**, **app-table-description**, dan **app-table-search**. Letakkan judul dan deskripsi di kiri serta pencarian di kanan. Pada ponsel, pencarian dapat memenuhi lebar kartu.

- Header tabel memakai latar #EFF7F5 dan teks #62858B. Header dan sel angka serta kolom **Actions** rata kanan dengan **text-end**.
- Pada contoh dengan filter tahun, kolom **Bulan** cukup menampilkan nama bulan.
- Kolom **Actions** memakai **btn-group** dengan satu tombol dropdown dan menu **dropdown-menu-end**. **Detail** membuka modal baris, sedangkan **Salin** menyalin isi baris. Kolom aksi dikecualikan dari pencarian.
- Batasi scroll horizontal pada area tabel. Footer meletakkan jumlah baris dan pilihan ukuran halaman di kiri, lalu pagination di kanan.

Untuk inisialisasi manual, panggil **initDataTable()** dan tandai tabel dengan atribut **data-datatable="manual"**. Gunakan pesan kosong dari helper. Untuk tabel besar, pertahankan strategi pencarian dan pagination modul yang berlaku.

[CSS kartu tabel](../../public/assets/css/root/table-card.css) · [CSS header dan footer tabel](../../public/assets/css/root/table-header.css) · [Helper DataTables](../../public/assets/js/root/filterdatatables.js)
