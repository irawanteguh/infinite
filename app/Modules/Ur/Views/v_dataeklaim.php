<div class="prem-page ur-dashboard ek-page">
    <section class="ur-hero app-page-heading">
        <div><h1>Data E-Klaim</h1><p>Telusuri data pasien, layanan, dan tarif klaim berdasarkan periode pulang.</p></div>
    </section>
    <section class="prem-card ur-filterbar app-filter-card" aria-labelledby="ek-filter-title">
        <div class="ur-filter-heading app-filter-heading"><span class="prem-eyebrow">CAKUPAN DATA</span><h2 id="ek-filter-title"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Filter E-Klaim</h2><p>Pilih periode pulang dan jenis layanan untuk menyesuaikan ringkasan dan daftar klaim.</p></div>
        <div class="ur-filters app-filter-fields">
<div class="ur-filter"><label for="ek-month">Bulan pulang</label><select id="ek-month" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="">Semua bulan</option><option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option><option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option><option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option><option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option></select></div>
            <div class="ur-filter"><label for="ek-service">Jenis layanan</label><select id="ek-service" name="service" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true"><option value="">Semua layanan</option><option value="1">Rawat inap</option><option value="2">Rawat jalan</option></select></div>
        </div>
        <div class="ek-filter-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Ringkasan dan tabel mengikuti periode serta layanan terpilih. Data berdasarkan tanggal pulang pasien.</div>
        <div class="ur-scope app-filter-scope"><i class="bi bi-calendar-range" aria-hidden="true"></i><span>Periode tersedia mengikuti data TXT E-Klaim yang telah diunggah.</span></div>
    </section>
    <section class="ek-summary" aria-label="Ringkasan periode terpilih">
        <?php foreach ([['count','Jumlah episode','bi-files','Episode pada periode terpilih'],['hospital','Total tarif RS','bi-hospital','Akumulasi biaya rumah sakit'],['cbg','Total INA-CBG','bi-cash-stack','Akumulasi tarif INA-CBG'],['idrg','Total iDRG','bi-calculator','Akumulasi tarif iDRG']] as $metric): ?>
        <div class="card ek-metric"><div class="ek-metric-label"><?= $metric[1] ?><i class="bi <?= $metric[2] ?>" aria-hidden="true"></i></div><strong id="ek-<?= $metric[0] ?>">—</strong><small><?= $metric[3] ?></small><?php if (in_array($metric[0], ['cbg', 'idrg'], true)): ?><small class="ek-difference" id="ek-<?= $metric[0] ?>-difference" aria-live="polite"></small><?php endif; ?></div>
        <?php endforeach; ?>
    </section>
    <section class="card ek-panel" aria-labelledby="ek-table-title">
        <div class="ek-table-heading"><div><h2 id="ek-table-title">Daftar E-Klaim</h2><p>Ringkasan pasien dan tarif. Buka detail untuk melihat rincian episode.</p></div><div class="ek-search"><i class="bi bi-search" aria-hidden="true"></i><label for="searchtable" class="visually-hidden">Cari data e-klaim</label><input type="search" class="form-control form-control-solid" placeholder="Cari pasien, SEP, atau diagnosis…" id="searchtable"></div></div>
        <div id="ek-status" class="ek-status" role="status" aria-live="polite"></div>
        <div class="ek-table-container">
            <table class="table align-middle table-row-dashed gy-2 fs-8" id="dataraweklaim_table" data-datatable="manual">
                <thead><tr><th scope="col">No.</th><th scope="col">Pasien</th><th scope="col">Nomor RM</th><th scope="col">No. SEP</th><th scope="col">Tgl masuk</th><th scope="col">Tgl pulang</th><th scope="col">DPJP</th><th scope="col">Layanan</th><th scope="col" class="text-end">Tarif RS</th><th scope="col" class="text-end">INA-CBG</th><th scope="col" class="text-end">iDRG</th><th scope="col" class="text-end">Detail</th></tr></thead>
                <tbody id="resultdataraweklaim"></tbody>
            </table>
        </div>
        <div class="ek-table-info" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div>Klik Detail untuk melihat nomor kartu, lama perawatan, diagnosis, dan prosedur. Pencarian mencakup rincian yang tersembunyi.</div></div>
    </section>
</div>
