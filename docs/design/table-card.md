# Kartu tabel

[Indeks](README.md)

Gunakan **app-table-card**, **app-table-heading**, **app-table-title**, **app-table-description**, dan **app-table-search**. Letakkan judul dan deskripsi di kiri serta pencarian di kanan. Pada ponsel, pencarian dapat memenuhi lebar kartu.

Header kartu memakai padding atas dan samping 24px, dengan jarak 20px menuju isi tabel. Isi kartu memakai padding samping dan bawah 24px. Pada layar hingga 575px, padding menjadi 16px. Judul dan deskripsi berada dalam satu pembungkus, dengan jarak 5px di atas deskripsi dan tanpa margin bawah. Gunakan aturan bersama di `table-card.css` agar header dan isi tabel sejajar.

- Header tabel memakai latar #EFF7F5 dan teks #62858B. Header dan sel angka serta kolom **Actions** rata kanan dengan **text-end**.
- Pada contoh dengan filter tahun, kolom **Bulan** cukup menampilkan nama bulan.
- Kolom **Actions** memakai **btn-group** dengan satu tombol dropdown dan menu **dropdown-menu-end**. **Detail** membuka modal baris, sedangkan **Salin** menyalin isi baris. Kolom aksi dikecualikan dari pencarian.
- Batasi scroll horizontal pada area tabel. Footer meletakkan jumlah baris dan pilihan ukuran halaman di kiri, lalu pagination di kanan.
- Pemilih jumlah baris memakai Select2 melalui helper bersama. Dropdown ditempelkan ke body, atau modal terdekat untuk tabel di modal, agar pilihan tampil utuh melewati batas kartu dan area scroll. Jangan menempatkan dropdown di dalam pembungkus scroll tabel.

Untuk inisialisasi manual, panggil **initDataTable()** dan tandai tabel dengan atribut **data-datatable="manual"**. Gunakan pesan kosong dari helper. Untuk tabel besar, pertahankan strategi pencarian dan pagination modul yang berlaku.

[CSS kartu tabel](../../public/assets/css/root/table-card.css) · [CSS header dan footer tabel](../../public/assets/css/root/table-header.css) · [Helper DataTables](../../public/assets/js/root/filterdatatables.js)
