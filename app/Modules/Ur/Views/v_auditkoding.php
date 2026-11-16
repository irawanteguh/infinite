<link rel="stylesheet" href="<?= base_url('assets/css/ur/dashboard.css') ?>">

<div id="ur-executive" class="ur-dashboard">
<?php include APPPATH . 'Modules/Ur/Pageheader/auditkoding.php'; ?>
<div class="ur-filterbar"><label for="audit-care">Jenis layanan</label><select id="audit-care" class="form-select w-auto"><option value="">Semua layanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select><span class="ur-filter-note">Berdasarkan tahun dan tanggal pulang</span></div>
<div id="audit-status" role="status"></div><section id="audit-kpis" class="ur-kpis"></section>


<section class="card ur-panel mb-5"><h2>Perbandingan UR dan riwayat pending klaim</h2><div id="audit-comparison"></div></section><section class="card ur-panel mb-5"><h2>Analisis dan rencana tindak lanjut</h2><div id="audit-analysis"></div></section><section class="card ur-panel mb-5"><h2>Aturan pemeriksaan dari riwayat verifikasi klaim</h2><p>Algoritma verifikasi yang tercatat pada rumah sakit dan tahun terpilih digunakan sebagai daftar pemeriksaan. Jumlah menunjukkan episode pada riwayat verifikasi, bukan hasil penerapan aturan ke seluruh data UR.</p><div id="audit-pending-rules"></div></section>
<div class="ur-method">Indikator merupakan pemicu pemeriksaan dokumentasi, bukan kesimpulan kesalahan koding. Satu episode dapat memenuhi beberapa indikator sehingga jumlah indikator tidak boleh dijumlahkan sebagai jumlah kasus unik. Diagnosis gejala dapat sesuai dengan hasil evaluasi klinis. Prosedur kosong tidak otomatis menunjukkan ketidaklengkapan. Penetapan diagnosis utama harus ditelaah berdasarkan rekam medis. Tarif aktual ditampilkan tanpa simulasi perubahan klaim.</div></div>
