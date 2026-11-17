<link rel="stylesheet" href="<?= base_url('assets/css/ur/dashboard.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/ur/simulation.css') ?>">
<div id="ur-executive" class="ur-dashboard ur-simulation">
<?php include APPPATH . 'Modules/Ur/Pageheader/analystslsimulation.php'; ?>
<section class="ur-filterbar sim-filter app-filter-card" aria-labelledby="sim-filter-title">
    <div class="app-filter-heading"><h2 id="sim-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Atur skenario simulasi</h2><p>Pilih periode dan besaran perpindahan kasus.</p></div>
    <div class="sim-filter-fields app-filter-fields">
        <div class="sim-filter-field"><label for="sim-year">Tahun pulang</label><select id="sim-year" class="form-select"><?= $periode ?></select></div>
        <div class="sim-filter-field"><label for="sim-rate">Persentase perpindahan</label><select id="sim-rate" class="form-select"><option value="0">0%</option><option value="5">5%</option><option value="10" selected>10%</option><option value="15">15%</option><option value="20">20%</option></select></div>
    </div>
    <div class="sim-filter-hint app-filter-scope"><span aria-hidden="true">↻</span> Hasil diperbarui otomatis saat filter berubah</div>
</section>
<section id="sim-kpis" class="ur-kpis" aria-label="Ringkasan simulasi severity"></section>
<div class="alert alert-warning">Skenario memindahkan sebagian kasus SL I ke SL II dan sebagian kasus SL II aktual ke SL III. Ini adalah analisis sensitivitas, bukan target peningkatan severity atau estimasi klaim yang dapat diajukan. Perubahan kode harus didukung dokumentasi klinis dan hasil grouping.</div>
<div id="sim-status" role="status"></div>
<section class="card ur-panel mb-5"><h2>Perubahan distribusi kasus per severity</h2><p>Jumlah awal → perpindahan kasus → jumlah akhir, disertai dampak nilai INA-CBG pada setiap severity.</p><p class="sim-explainer">Nilai skenario = episode sesudah simulasi × tarif rata-rata aktual severity yang sama. Selisih nilai dibandingkan dengan total aktual.</p><div id="sim-distribution" class="row g-5"></div><div id="sim-reconciliation" class="sim-net" aria-live="polite"></div></section>
<section class="card ur-panel"><h2>Asumsi perhitungan</h2><p>Perpindahan dihitung dari jumlah episode aktual masing-masing severity dan dibulatkan ke episode terdekat. Tarif skenario menggunakan rata-rata TOTAL_TARIF aktual severity tujuan pada tahun terpilih. Kasus yang berpindah dari SL I tidak dipindahkan lagi ke SL III. Simulasi hanya mencakup episode dengan severity I, II, dan III. Simulasi tidak memperhitungkan perubahan kelompok INA-CBG, diagnosis, prosedur, atau karakteristik pasien.</p><p>Jika kelompok tujuan tidak memiliki episode aktual, nilai skenario tidak dihitung karena tarif pembanding tidak tersedia.</p></section></div>
