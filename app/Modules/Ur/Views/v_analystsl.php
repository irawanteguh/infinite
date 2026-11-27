<div id="ur-executive" class="prem-page pe-dashboard ur-dashboard sl-page">
<?php include APPPATH . 'Modules/Ur/Pageheader/analystsl.php'; ?>
<section class="ur-filterbar app-filter-card" aria-labelledby="sl-filter-title">
<div class="app-filter-heading"><h2 id="sl-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter tingkat keparahan</h2><p>Pilih bulan pulang untuk menyesuaikan ringkasan dan profil severity.</p></div>
<div class="app-filter-fields"><div class="ur-filter"><label for="sl-month">Bulan pulang</label><select id="sl-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
<option value="">Semua bulan</option>
<?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $month): ?>
<option value="<?= $i+1 ?>"><?= $month ?></option>
<?php endforeach; ?></select></div><span class="badge badge-light-primary">Rawat inap</span></div>
<div class="app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span>Periode mengikuti tanggal pulang dan tahun pada toolbar.</span></div>
</section>
<div id="sl-status" class="ur-status" role="status" aria-live="polite"></div>
<section id="sl-kpis" class="ur-kpis" aria-label="Ringkasan severity"></section>
<section class="card card-flush ur-panel app-table-card mb-5">
<div class="ur-panel-head app-table-heading"><div><h2 class="app-table-title">Profil pelayanan per severity</h2><p class="app-table-description">Episode, lama rawat, dan tarif untuk severity I, II, dan III pada rawat inap.</p></div></div>
<div class="ur-table-wrap"><table class="table table-row-dashed align-middle ur-table fs-8 gy-2"><thead><tr><th>Severity</th><th>Episode</th><th>Proporsi</th><th>Rata-rata LOS</th><th>Tarif RS</th><th>Total INA-CBG</th><th>INA-CBG per episode</th><th>Selisih tarif</th></tr></thead><tbody id="sl-profile"></tbody></table></div>
</section>
<section class="card card-flush ur-panel mb-5"><div class="ur-panel-head"><div><h2>Perkembangan komposisi severity</h2><p>Jumlah episode per bulan berdasarkan hasil grouping.</p></div></div><div id="sl-chart" class="ur-chart"></div></section>
<section class="card card-flush ur-panel mb-5"><div class="ur-panel-head"><div><h2>Analisis dan rekomendasi tindak lanjut</h2><p>Prioritas telaah berdasarkan lama rawat dan pemakaian sumber daya.</p></div></div><div id="sl-analysis"></div></section>
<div class="sl-method" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Dasar pembacaan.</strong> Severity mengikuti hasil grouping yang tercatat. Perbedaan tarif dan lama rawat juga dipengaruhi komposisi diagnosis, prosedur, dan karakteristik kasus. Perubahan severity harus berdasarkan dokumentasi klinis dan hasil grouping yang sah. Bulan terakhir dapat belum lengkap.</div></div>
</div>