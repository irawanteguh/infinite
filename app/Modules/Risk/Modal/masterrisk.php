<?php
$codeError = session()->getFlashdata('masterrisk_error');
$categoryError = session()->getFlashdata('masterrisk_category_error');
$modalData = $riskMasterModal ?? ['departments' => [], 'ready' => false, 'categoriesReady' => false];
$codeInput = static function ($key) use ($codeError) {
    $value = $codeError ? old($key, '', false) : '';
    return esc(is_scalar($value) ? (string) $value : '');
};
?>
<div class="modal fade" id="modal_add_masterrisk" tabindex="-1" aria-labelledby="mr-modal-title" aria-hidden="true" data-reopen="<?= $codeError ? '1' : '0' ?>">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-650px">
        <div class="modal-content d-flex flex-column overflow-hidden">
            <div class="modal-header pb-0 border-0">
                <h2 id="mr-modal-title" class="mb-3"><?= $codeError && $codeInput('code_id') !== '' ? 'Edit Kode Risiko' : 'Tambah Kode Risiko' ?></h2>
                <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup">
                    <span class="svg-icon svg-icon-1"><i class="bi bi-x-lg" aria-hidden="true"></i></span>
                </button>
            </div>
            <form action="<?= site_url('risk/masterrisk/save') ?>" method="post" id="mr-form" class="d-flex flex-column overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="code_id" value="<?= $codeInput('code_id') ?>">
                <div class="modal-body overflow-auto">
                    <div class="text-start mb-7"><div class="text-muted fw-bold fs-6">Lengkapi kode risiko dan department penanggung jawab.</div></div>
                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <label for="mr-code" class="required form-label fw-bold">Kode Risiko</label>
                            <input type="text" id="mr-code" name="code" class="form-control form-control-solid" maxlength="60" pattern="[A-Za-z0-9][A-Za-z0-9._\/\-]{0,59}" placeholder="Contoh: R3.7" value="<?= $codeInput('code') ?>" required>
                        </div>
                        <div class="col-md-12 mb-5">
                            <label for="mr-unit" class="required form-label fw-bold">Department</label>
                            <select id="mr-unit" name="department_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_masterrisk" data-placeholder="Pilih department" data-allow-clear="false" required>
                                <option value=""></option>
                                <?php foreach ($modalData['departments'] as $department): ?>
                                <option value="<?= esc($department['department_id']) ?>" <?= $codeInput('department_id') === esc($department['department_id']) ? 'selected' : '' ?>><?= esc($department['department']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" <?= !$modalData['ready'] ? 'disabled' : '' ?>>Simpan Kode Risiko</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_add_riskcategory" tabindex="-1" aria-labelledby="category-modal-title" aria-hidden="true" data-reopen="<?= $categoryError ? '1' : '0' ?>">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-650px">
        <div class="modal-content d-flex flex-column overflow-hidden">
            <div class="modal-header pb-0 border-0">
                <h2 id="category-modal-title" class="mb-3">Tambah Kategori Risiko</h2>
                <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup">
                    <span class="svg-icon svg-icon-1"><i class="bi bi-x-lg" aria-hidden="true"></i></span>
                </button>
            </div>
            <form action="<?= site_url('risk/masterrisk/savecategory') ?>" method="post" id="risk-category-form" class="d-flex flex-column overflow-hidden">
                <?= csrf_field() ?>
                <div class="modal-body overflow-auto">
                    <div class="text-start mb-7"><div class="text-muted fw-bold fs-6">Lengkapi nama kategori risiko dan keterangannya.</div></div>
                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <label for="category-name" class="required form-label fw-bold">Nama Kategori Risiko</label>
                            <input type="text" id="category-name" name="category_name" class="form-control form-control-solid" maxlength="100" value="<?= $categoryError ? old('category_name', '') : '' ?>" required>
                        </div>
                        <div class="col-md-12 mb-5">
                            <label for="category-description" class="form-label fw-bold">Keterangan</label>
                            <textarea id="category-description" name="category_description" class="form-control form-control-solid" rows="5" maxlength="12000"><?= $categoryError ? old('category_description', '') : '' ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" <?= !$modalData['categoriesReady'] ? 'disabled' : '' ?>>Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
