<div id="fishboneRoot" data-mode="list" data-endpoint="<?= esc(site_url('qi/fishbone')) ?>" data-indicator-ref="<?= esc($linkedIndicator->transaksi_id ?? '') ?>" data-department="<?= esc($linkedIndicator->department_id ?? '') ?>" data-period="<?= esc($linkedIndicator->periode_id ?? '') ?>" class="d-flex flex-wrap flex-stack mb-6">
    <div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-5"><h3 class="card-title fw-bolder">Daftar Analisis</h3></div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed gy-3" id="fishboneTable">
                <thead><tr class="text-muted fw-bold"><th>Masalah</th><th>Indikator</th><th>Departemen</th><th>Periode</th><th class="text-end">Aksi</th></tr></thead>
                <tbody id="fishboneRows" class="text-gray-700 fw-semibold"></tbody>
            </table>
        </div>
        <div id="fishboneEmpty" class="text-muted text-center py-8">Belum ada analisis. Buat analisis untuk mulai.</div>
    </div>
</div>
