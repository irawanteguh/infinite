<div id="fishboneRoot" data-endpoint="<?= esc(site_url('qi/fishbone')) ?>" data-indicator-ref="<?= esc($linkedIndicator->transaksi_id ?? '') ?>" data-department="<?= esc($linkedIndicator->department_id ?? '') ?>" data-period="<?= esc($linkedIndicator->periode_id ?? '') ?>" class="d-flex flex-wrap flex-stack mb-6">
    <div><h2 class="text-gray-900 fw-bolder mb-2">Analisis Fishbone</h2><p class="text-muted mb-0">Identifikasi penyebab masalah mutu menggunakan kategori 6M.</p>
    <?php if (!empty($linkedIndicator)): ?><div class="text-primary fw-bold mt-2"><i class="bi bi-link-45deg me-1"></i>Indikator: <?= esc($linkedIndicator->indikator) ?> — <?= esc($linkedIndicator->department) ?> (<?= esc($linkedIndicator->tahun) ?>)</div><?php endif; ?></div>
</div>
<div class="row g-5">
    <div class="col-xl-4">
        <div class="card h-100"><div class="card-header border-0 pt-5"><h3 class="card-title fw-bolder">Daftar Analisis</h3></div>
            <div class="card-body pt-0"><div class="table-responsive"><table class="table align-middle table-row-dashed gy-3" id="fishboneTable"><thead><tr class="text-muted fw-bold"><th>Masalah</th><th>Departemen</th><th>Periode</th><th></th></tr></thead><tbody id="fishboneRows" class="text-gray-700 fw-semibold"></tbody></table></div><div id="fishboneEmpty" class="text-muted text-center py-8">Belum ada analisis. Buat analisis untuk mulai.</div></div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card"><div class="card-header border-0 pt-5"><div><h3 class="card-title fw-bolder">Diagram sebab-akibat</h3><div class="text-muted fs-7" id="fishboneDepartmentLabel">Pilih analisis dari daftar</div></div><div class="card-toolbar"><button class="btn btn-sm btn-light-primary" id="fishboneAddCauseButton" disabled><i class="bi bi-plus-lg me-1"></i>Tambah penyebab</button></div></div>
            <div class="card-body pt-0"><div class="table-responsive"><div id="fishboneDiagram" class="min-w-700px"></div></div><div id="fishboneCauseList" class="row g-4 mt-2"></div></div>
        </div>
    </div>
</div>
