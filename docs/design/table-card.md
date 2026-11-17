# Card tabel

Acuan: Daftar E-Klaim. [Indeks](README.md).

Sumber global: public/assets/css/table-card.css, table-header.css, serta public/assets/js/root/filterdatatables.js.

## Header dan area isi

Gunakan app-table-card pada card, app-table-heading pada header, app-table-title untuk judul, app-table-description untuk deskripsi, dan app-table-search untuk pembungkus input search.

Judul/deskripsi di kiri dan search di kanan. Padding desktop 24px, ponsel 16px. Judul 20px, deskripsi 14px, search lebar 320px dengan tinggi minimal 42px; di ponsel search memenuhi lebar. Header ke tabel berjarak 20px. Pembungkus app-table-search sudah menyediakan ikon; tidak perlu ikon pencarian kedua.

Thead tebal 700, latar #eff7f5, teks #62858b pada tabel UR. Kolom identitas rata kiri, nilai angka/uang rata kanan. Gunakan scope=col pada header baru. Pisahkan RM dari nama pasien bila perlu. Data panjang seperti diagnosis dapat dipindahkan ke Detail yang bisa dibuka/tutup.

## Inisialisasi

~~~js
initDataTable('#page-table', '#page-search', 10);
~~~

Seluruh inisialisasi DataTables memakai helper tersebut. data-datatable=manual berarti script halaman akan memanggil helper sendiri; ini bukan pengganti pemanggilan helper. Jangan membuat instance DataTables kedua untuk tabel yang sama.

Helper dapat melengkapi header/search pada card satu tabel. Card dengan beberapa tabel tetap memerlukan struktur header/kontrol yang jelas di view. Tetapkan teks judul/deskripsi secara eksplisit untuk modul baru, jangan mengandalkan fallback generik.

Footer: dropdown tanpa label Tampilkan dan Showing … records di kiri; pagination di kanan; halaman aktif teal. Scroll horizontal dibatasi pada tabel.

Rekap bulanan UR memakai paging:false, searching:false, info:false, lengthChange:false, dom:'t' agar semua bulan tampil. Tabel detail besar memakai serverSide:true, pilihan 10/25/50/100, dan pencarian dengan jeda 400ms. Jangan mengirim seluruh data tahunan untuk pagination di browser.

Baris total memakai tfoot. Jelaskan apakah total mengikuti filter atau pencarian. Kolom persentase harus didampingi pembilang dan pembanding, misalnya Total Episode, Episode Positif, dan % Episode Positif. Pesan tabel kosong mengikuti helper; lihat [status](info-state.md).
