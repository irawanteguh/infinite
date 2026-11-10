<link rel="stylesheet" href="<?= base_url('assets/css/ur/dashboard.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/ur/simulation.css') ?>">
<div id="ur-executive" class="ur-dashboard ur-simulation">
<section class="ur-hero"><div><div class="ur-eyebrow">UTILIZATION REVIEW / SIMULASI SEVERITY</div><h1>Simulasi komposisi severity dan tarif</h1><p>Perbandingan skenario dengan data aktual rawat inap pada tahun terpilih.</p></div></section>
<section class="ur-filterbar sim-filter" aria-labelledby="sim-filter-title">
    <div class="sim-filter-intro"><span class="sim-filter-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2" fill="currentColor"/><circle cx="16" cy="12" r="2" fill="currentColor"/><circle cx="10" cy="18" r="2" fill="currentColor"/></svg></span><div><h2 id="sim-filter-title">Atur skenario simulasi</h2><p>Pilih periode dan besaran perpindahan kasus.</p></div></div>
    <div class="sim-filter-fields">
        <div class="sim-filter-field"><label for="sim-year">Tahun pulang</label><select id="sim-year" class="form-select"><?= $periode ?></select></div>
        <div class="sim-filter-field"><label for="sim-rate">Persentase perpindahan</label><select id="sim-rate" class="form-select"><option value="0">0%</option><option value="5">5%</option><option value="10" selected>10%</option><option value="15">15%</option><option value="20">20%</option></select></div>
    </div>
    <div class="sim-filter-hint"><span aria-hidden="true">↻</span> Hasil diperbarui otomatis saat filter berubah</div>
</section>
<section id="sim-kpis" class="ur-kpis" aria-label="Ringkasan simulasi severity"></section>
<div class="alert alert-warning">Skenario memindahkan sebagian kasus SL I ke SL II dan sebagian kasus SL II aktual ke SL III. Ini adalah analisis sensitivitas, bukan target peningkatan severity atau estimasi klaim yang dapat diajukan. Perubahan kode harus didukung dokumentasi klinis dan hasil grouping.</div>
<div id="sim-status" role="status"></div>
<section class="card ur-panel mb-5"><h2>Perubahan distribusi kasus per severity</h2><p>Jumlah awal → perpindahan kasus → jumlah akhir, disertai dampak nilai INA-CBG pada setiap severity.</p><p class="sim-explainer">Nilai skenario = episode sesudah simulasi × tarif rata-rata aktual severity yang sama. Selisih nilai dibandingkan dengan total aktual.</p><div id="sim-distribution" class="row g-5"></div><div id="sim-reconciliation" class="sim-net" aria-live="polite"></div></section>
<section class="card ur-panel"><h2>Asumsi perhitungan</h2><p>Perpindahan dihitung dari jumlah episode aktual masing-masing severity dan dibulatkan ke episode terdekat. Tarif skenario menggunakan rata-rata TOTAL_TARIF aktual severity tujuan pada tahun terpilih. Kasus yang berpindah dari SL I tidak dipindahkan lagi ke SL III. Simulasi hanya mencakup episode dengan severity I, II, dan III. Simulasi tidak memperhitungkan perubahan kelompok INA-CBG, diagnosis, prosedur, atau karakteristik pasien.</p><p>Jika kelompok tujuan tidak memiliki episode aktual, nilai skenario tidak dihitung karena tarif pembanding tidak tersedia.</p></section></div>
