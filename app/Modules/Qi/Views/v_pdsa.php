<div id="pdsaRoot" data-endpoint="<?= esc($endpoint) ?>" data-indicator-ref="<?= esc($linkedIndicator->transaksi_id ?? '') ?>" data-department="<?= esc($linkedIndicator->department_id ?? '') ?>" data-period="<?= esc($linkedIndicator->periode_id ?? '') ?>" class="d-flex flex-wrap flex-stack mb-6">
    <?php if (!empty($linkedIndicator)): ?><div class="text-primary fw-bold mt-2"><i class="bi bi-link-45deg me-1"></i>Indikator: <?= esc($linkedIndicator->indikator) ?> — <?= esc($linkedIndicator->department) ?> (<?= esc($linkedIndicator->tahun) ?>)</div><?php endif; ?>
</div>
<div class="card">
    <div class="card-header border-0 pt-5"><h3 class="card-title fw-bolder">Daftar PDSA</h3></div>
    <div class="card-body pt-0">
        <div class="table-responsive"><table class="table align-middle table-row-dashed gy-3"><thead><tr class="text-muted fw-bold"><th>Judul</th><th>Unit / periode</th><th>Item PDSA</th><th class="text-end">Aksi</th></tr></thead><tbody id="pdsaRows"></tbody></table></div>
        <div id="pdsaEmpty" class="text-muted text-center py-8">Belum ada header PDSA.</div>
    </div>
</div>
