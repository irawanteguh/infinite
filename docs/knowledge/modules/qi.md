# QI — indikator dan laporan mutu

Banner Master Indikator mengikuti posisi kiri atas dengan pembungkus `app-banner-heading`. Header tabel mengikuti variabel palet kartu tabel bersama.

| Alur | Titik masuk |
| --- | --- |
| Definisi dan periode | [Masterindikator](../../../app/Modules/Qi/Controllers/Masterindikator.php) dan [Periodepelaporan](../../../app/Modules/Qi/Controllers/Periodepelaporan.php) mengelola definisi, periode, dan tim periode. |
| Indikator unit | [Indikatorunit](../../../app/Modules/Qi/Controllers/Indikatorunit.php) menangani pengajuan, PIC, input bulanan, lampiran, dan data tim. [IndikatorunitModel](../../../app/Modules/Qi/Models/IndikatorunitModel.php) memeriksa hubungan pengguna dengan indikator/unit. Pengaturan tenggat dan approval ada di [Organization](organization.md). |
| Pemantauan | [Summary](../../../app/Modules/Qi/Controllers/Summary.php) dan [Summarycompare](../../../app/Modules/Qi/Controllers/Summarycompare.php) menggunakan [SummaryModel](../../../app/Modules/Qi/Models/SummaryModel.php) untuk ringkasan, tren, dan pembandingan periode. |
| Tindak lanjut | [Fishbone](../../../app/Modules/Qi/Controllers/Fishbone.php) dan [Pdsa](../../../app/Modules/Qi/Controllers/Pdsa.php) menangani analisis penyebab dan siklus perbaikan. |
| Laporan | [Summaryreport](../../../app/Modules/Qi/Controllers/Summaryreport.php) menyediakan `periods`, `generate`, `progress`, dan `viewreport`. [SummaryreportModel](../../../app/Modules/Qi/Models/SummaryreportModel.php) memasok data, [StoredSummaryReport](../../../app/Modules/Qi/Services/StoredSummaryReport.php) menyimpan hasil menurut group/org/periode, dan mPDF merender PDF. |

Model memakai grup default. Tabel utama `dt01_qi_indikator_ms`, `dt01_qi_indikator_hd`, `dt01_qi_indikator_it`, `dt01_qi_periode_mutu`, `dt01_qi_komite_mutu_hd`, tabel Fishbone/PDSA, dan master department. Route `qi/<controller>` mengikuti login/grant. Cakupan group/org dan unit/PIC bergantung aksi. Rumus ringkasan ada di [aturan bisnis](../business-rules.md).

[Views](../../../app/Modules/Qi/Views/), [aset QI](../../../public/assets/js/qi/), dan direktori pendukung `Modal`, `Toolbar`, `Team`, `Progress` memuat form serta tampilan laporan. Target aktif, isi periode dan kecocokan hasil PDF dengan data deployment belum diverifikasi.

Master Indikator memakai banner Betawi dan kartu tabel bersama, pencarian, serta toolbar Tambah indikator/Perbarui. Pengambilan data, penyimpanan, dan perubahan status memakai showDataLoading di beforeSend. Form mencegah submit berulang dan tabel ditandai manual untuk inisialisasi DataTables. File UI terkait berada di view, Modal, Toolbar, CSS, dan JS masterindikator.

Summary memakai banner modul bersama dan kartu tabel untuk rincian. Pemuatan periode memakai showDataLoading sebelum AJAX, dengan hasil dan pesan gagal ditampilkan setelah modal pemuatan selesai.

Kartu Summary mengikuti kartu total teal muda dan rincian putih pada Testing Page. Tren memakai garis Jan–Des. Dimensi mutu ditampilkan sebagai batang horizontal. Peringkat menampilkan enam departemen dengan ketercapaian tertinggi, dengan seluruh departemen tetap tersedia pada tabel detail. Warna batang memakai teal tanpa klasifikasi ambang tambahan.

Layout Summary menempatkan tren bulanan selebar halaman. Grafik dimensi mutu dan peringkat departemen berdampingan dengan lebar setengah pada desktop, serta bertumpuk pada ponsel. Kartu jenis indikator memakai ikon teal soft dan hierarki judul/deskripsi bersama.

Grafik dimensi dan peringkat departemen memakai daftar progress bar seperti Grafik peringkat Testing Page. Nilai dan panjang batang menunjukkan persentase ketercapaian target pada skala 0–100%, dengan urutan terbesar terlebih dahulu. Keterangan per departemen memuat jumlah indikator yang sudah dinilai.

Tren ketercapaian bulanan mengikuti grafik garis Testing Page dengan tinggi 320 px, seri teal dan teal muda, garis lurus, marker, grid putus-putus, legenda di atas, dan tooltip bersama.

Header tabel keterlambatan memakai satu judul dan paragraf deskripsi dalam pembungkus bersama. Paragraf dikenali helper DataTables sehingga deskripsi generik tidak ditambahkan.

Header tabel agregat dan detail memakai paragraf deskripsi agar helper tabel tidak menambahkan deskripsi generik. Navigasi bagian Summary memakai tombol tautan berikon yang membungkus mengikuti lebar layar.

Navigasi bagian Summary memakai pembungkus dan tombol seperti Filter pertanyaan Testing Page. Tautan menuju bagian halaman tetap tersedia dan tombol membungkus pada layar sempit.

Tipografi Summary menggunakan Poppins sesuai template dan Testing Page, dengan judul kartu semibold. Grafik tren memakai font Poppins yang sama.

Pengantar agregat memakai card informasi alert-info dengan penjelasan cakupan seluruh unit. Pemilihan Global, Mutu Nasional, dan Prioritas Rumah Sakit menggunakan markup Filter pertanyaan Testing Page berupa app-filter-card, app-filter-fields, app-filter-options, dan tombol btn btn-sm btn-light-primary. Atribut aria-pressed mengikuti pilihan aktif.

Filter jenis agregat mengikuti struktur lengkap Filter pertanyaan Testing Page, dengan heading, bidang tombol, dan status pilihan. Klik tombol menampilkan panel terkait, memperbarui aria-pressed dan status, lalu menyesuaikan lebar DataTables yang menjadi terlihat.

Card navigasi bagian memiliki judul Navigasi ringkasan dan deskripsi tujuan tautan, mengikuti app-filter-heading pada Testing Page.
