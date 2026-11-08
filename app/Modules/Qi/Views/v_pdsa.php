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

<div class="modal fade" id="pdsaModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="pdsaForm">
                <div class="modal-header"><div><h3 id="pdsaFormTitle" class="modal-title fw-bolder">Buat header PDSA</h3><div class="text-muted fs-7 mt-1">Buat header perbaikan dan tentukan indikator yang akan diperbaiki.</div></div><button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg"></i></button></div>
                <div class="modal-body">
                <input type="hidden" name="pdsa_id" id="pdsaId">
                <div class="row g-4 mb-4">
                        <div class="col-12"><label class="form-label required">Judul perubahan</label><input class="form-control" name="title" required maxlength="255" placeholder="Contoh: Mempercepat asesmen awal pasien"></div>
                    <div class="col-md-6"><label class="form-label required">Departemen</label><select class="form-select" name="department_id" required><option value="">Pilih departemen</option><?php foreach ($departments as $row): ?><option value="<?= esc($row->department_id) ?>"><?= esc($row->department) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Periode</label><select class="form-select" name="periode_id"><option value="">Tidak terikat periode</option><?php foreach ($periods as $row): ?><option value="<?= esc($row->periode_id) ?>"><?= esc($row->tahun) ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label">Indikator mutu terkait</label><select class="form-select" name="indikator_ref_id"><option value="">Tidak terkait indikator tertentu</option><?php foreach ($indicators as $row): ?><option value="<?= esc($row->transaksi_id) ?>" data-department="<?= esc($row->department_id ?? '') ?>"><?= esc($row->indikator . ' — ' . $row->department . ' (' . $row->tahun . ')') ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label class="form-label required">Masalah / peluang perbaikan</label><textarea class="form-control" name="problem" rows="2" required></textarea></div>
                    <div class="col-12"><label class="form-label">Tujuan perbaikan</label><textarea class="form-control" name="aim" rows="3"></textarea></div>
                </div>
                <div id="pdsaMessage" class="small mt-3" role="status"></div>
                </div>
                <div class="modal-footer flex-shrink-0 bg-white"><button class="btn btn-light-danger me-auto d-none" type="button" id="pdsaArchive">Arsipkan</button><button class="btn btn-light" type="button" id="pdsaReset">Kosongkan</button><button class="btn btn-primary" type="submit">Simpan header</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="pdsaStagesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable"><div class="modal-content">
        <form id="pdsaStagesForm">
            <div class="modal-header"><div><h3 class="modal-title fw-bolder">Kelola item PDSA</h3><div id="pdsaStagesSubtitle" class="text-muted fs-7 mt-1"></div></div><button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg"></i></button></div>
            <div class="modal-body">
                <input type="hidden" name="pdsa_id">
                <?php foreach (['Plan' => 'Rencana dan prediksi', 'Do' => 'Pelaksanaan dan pengumpulan data', 'Study' => 'Hasil dan pembelajaran', 'Act' => 'Tindak lanjut'] as $phase => $label): ?>
                    <section class="border rounded p-4 mb-4" data-phase-section="<?= esc($phase) ?>"><div class="d-flex align-items-center justify-content-between mb-3"><div><div class="fw-bolder text-gray-800"><?= esc(strtoupper($phase)) ?></div><div class="text-muted fs-7"><?= esc($label) ?></div></div><button type="button" class="btn btn-sm btn-light-primary" data-add-item="<?= esc($phase) ?>"><i class="bi bi-plus-lg me-1"></i>Tambah item</button></div><div data-item-list="<?= esc($phase) ?>"></div></section>
                <?php endforeach; ?>
                <div id="pdsaStagesMessage" class="small mt-3" role="status"></div>
            </div>
            <div class="modal-footer flex-shrink-0 bg-white"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Simpan item</button></div>
        </form>
    </div></div>
</div>
