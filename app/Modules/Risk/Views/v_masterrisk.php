<?php
$error = session()->getFlashdata('masterrisk_error');
$success = session()->getFlashdata('masterrisk_success');
$categoryError = session()->getFlashdata('masterrisk_category_error');
$unitCount = count(array_unique(array_filter(array_column($codes, 'department_id'))));
$unmappedCount = count(array_filter($codes, static function ($code) { return empty($code['department']); }));
?>
<?php if ($error || $categoryError || $success): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') { return; }
    <?php if ($error || $categoryError): ?>Swal.fire({icon:'error', title:'Gagal', text:<?= json_encode($error ?: $categoryError, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>, confirmButtonText:'Tutup'});<?php else: ?>Swal.fire({icon:'success', title:'Berhasil', text:<?= json_encode($success, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>, timer:2200, showConfirmButton:false});<?php endif ?>
});
</script>
<?php endif ?>
<div class="risk-master-page">
<?php include APPPATH . 'Modules/Risk/Pageheader/masterrisk.php'; ?>
    <div class="row g-4 align-items-stretch">
        <div class="col-12 col-lg-6">
                <section class="card card-flush h-100 border shadow-sm" id="master-codes" >
                    <div class="card-header border-0 pt-6 align-items-center gap-3">
                        <div><h2 class="fs-5 mb-2">Kode risiko per departemen</h2><p>Hubungkan kode risiko dengan unit penanggung jawab.</p></div>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="position-relative risk-master-search my-1 w-100 w-md-250px"><svg class="risk-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4.5 4.5"></path></svg><label for="mr-search" class="visually-hidden">Cari kode atau departemen</label><input id="mr-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kode atau departemen…"></div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <?php if (!$ready): ?><div class="alert alert-warning">Tabel master kode risiko belum tersedia.</div><?php endif ?>
                        <?php if ($unmappedCount): ?><div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-5"><i class="bi bi-info-circle text-warning fs-3 me-3" aria-hidden="true"></i><span class="text-gray-800"><?= $unmappedCount ?> kode belum terhubung ke department.</span></div><?php endif ?>
                        <div class="table-responsive"><table id="datamasterrisk_table" class="table align-middle table-row-dashed gy-2"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-50px">#</th><th class="w-150px">Kode risiko</th><th>Departemen</th><th class="text-end pe-4 rounded-end w-100px">Actions</th></tr></thead><tbody class="fw-bold text-gray-600" id="mr-codes">
                        <?php foreach ($codes as $index => $code): ?><tr data-master-code><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-primary"><?= esc($code['code']) ?></span></td><td><?php if (!empty($code['department'])): ?><?= esc($code['department']) ?><?php else: ?><span class="badge badge-light-warning">Belum dipetakan</span><?php endif ?></td><td class="text-end pe-4"><div class="btn-group"><button type="button" class="btn btn-sm btn-light-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions</button><div class="dropdown-menu dropdown-menu-end"><button type="button" class="dropdown-item btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#modal_add_masterrisk" data-code-id="<?= esc($code['code_id']) ?>" data-code="<?= esc($code['code']) ?>" data-department-id="<?= esc($code['department_id']) ?>" aria-label="Edit kode <?= esc($code['code']) ?>"><i class="bi bi-pencil" aria-hidden="true"></i>Edit</button></div></div></td></tr><?php endforeach ?>
                        </tbody></table></div>
                    </div>
                </section>
        </div>
        <div class="col-12 col-lg-6"><?php include __DIR__ . '/v_masterrisk_category.php'; ?></div>
        <div class="col-12"><?php include __DIR__ . '/v_masterrisk_matrix.php'; ?></div>
    </div>
</div>
