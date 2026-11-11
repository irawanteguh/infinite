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
<div class="d-flex flex-column gap-6">
    <section class="card card-flush border-0 overflow-hidden position-relative" style="background:linear-gradient(115deg,#172554 0%,#1d4ed8 60%,#0891b2 100%)">
        <div class="position-absolute rounded-circle" style="width:260px;height:260px;right:-60px;top:-140px;background:rgba(255,255,255,.08)" aria-hidden="true"></div>
        <div class="position-absolute rounded-circle" style="width:180px;height:180px;right:100px;bottom:-130px;border:25px solid rgba(255,255,255,.06)" aria-hidden="true"></div>
        <div class="card-body p-6 p-lg-9 position-relative d-flex flex-wrap align-items-center justify-content-between gap-6">
            <div class="d-flex align-items-center gap-5">
                <div class="symbol symbol-60px d-none d-sm-flex"><span class="symbol-label" style="background:rgba(255,255,255,.14)"><i class="bi bi-shield-check text-white fs-2x" aria-hidden="true"></i></span></div>
                <div><div class="fw-bold fs-8 text-uppercase mb-3" style="color:#a5f3fc;letter-spacing:2px">Risk Register</div><h1 class="fs-1 fw-bolder text-white mb-3">Master Risiko</h1><p class="mb-0 fs-6" style="color:#dbeafe;max-width:520px">Referensi yang konsisten untuk mengidentifikasi, mengelompokkan, dan menilai risiko organisasi.</p></div>
            </div>
            <div class="rounded p-4" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2)"><div class="text-white fw-bold mb-2"><i class="bi bi-diagram-3 text-white me-2" aria-hidden="true"></i>Fondasi manajemen risiko</div><span class="fs-7" style="color:#dbeafe">Kode unit · Kategori risiko · Matriks 5 × 5</span></div>
        </div>
    </section>
    <div class="row g-4">
        <?php foreach ([
            ['codes', 'collection', '#1d4ed8', count($codes), 'Kode risiko', $unitCount . ' department terhubung'],
            ['categories', 'grid', '#6d28d9', count($categories), 'Kategori risiko', 'Definisi dan keterangan kategori'],
            ['matrix', 'table', '#047857', count($matrixCells), 'Kombinasi matriks', 'Pemetaan kemungkinan dan dampak · 5 × 5'],
        ] as $stat): ?>
        <div class="col-md-4"><div class="card card-flush h-100 border-0 shadow-sm" style="background-color:<?= $stat[2] ?>">
            <div class="card-body p-6 d-flex align-items-start gap-4">
                <div class="symbol symbol-45px"><span class="symbol-label" style="background:rgba(255,255,255,.18)"><i class="bi bi-<?= $stat[1] ?> text-white fs-2" aria-hidden="true"></i></span></div>
                <div><div class="fw-bold text-white fs-6 mb-2"><?= $stat[4] ?></div><strong class="d-block fs-2x fw-bolder text-white mb-2"><?= $stat[3] ?></strong><div class="fs-7 text-white"><?= $stat[5] ?></div></div>
            </div>
        </div></div>
        <?php endforeach ?>
    </div>
    <div class="row g-6 align-items-stretch">
        <div class="col-12 col-lg-6">
                <section class="card card-flush h-100 border shadow-sm" id="master-codes" style="border-top:3px solid #3b82f6 !important">
                    <div class="card-header border-0 pt-6 align-items-center gap-3">
                        <div><h2 class="fs-5 mb-2"><i class="bi bi-collection text-primary me-2" aria-hidden="true"></i>Kode risiko per department</h2><span class="text-muted fs-7">Hubungkan kode risiko dengan unit penanggung jawab.</span></div>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="position-relative my-1 w-100 w-md-250px"><i class="bi bi-search fs-3 position-absolute top-50 start-0 translate-middle-y ms-4 text-muted" aria-hidden="true"></i><label for="mr-search" class="visually-hidden">Cari kode atau department</label><input id="mr-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kode atau department…"></div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <?php if (!$ready): ?><div class="alert alert-warning">Tabel master kode risiko belum tersedia.</div><?php endif ?>
                        <?php if ($unmappedCount): ?><div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-5"><i class="bi bi-info-circle text-warning fs-3 me-3" aria-hidden="true"></i><span class="text-gray-800"><?= $unmappedCount ?> kode belum terhubung ke department.</span></div><?php endif ?>
                        <div class="table-responsive"><table id="datamasterrisk_table" class="table align-middle table-row-dashed gy-2"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-50px">#</th><th class="w-150px">Kode risiko</th><th>Department</th><th class="text-end pe-4 rounded-end w-100px">Aksi</th></tr></thead><tbody class="fw-bold text-gray-600" id="mr-codes">
                        <?php foreach ($codes as $index => $code): ?><tr data-master-code><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-primary"><?= esc($code['code']) ?></span></td><td><?php if (!empty($code['department'])): ?><?= esc($code['department']) ?><?php else: ?><span class="badge badge-light-warning">Belum dipetakan</span><?php endif ?></td><td class="text-end pe-4"><button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_add_masterrisk" data-code-id="<?= esc($code['code_id']) ?>" data-code="<?= esc($code['code']) ?>" data-department-id="<?= esc($code['department_id']) ?>" aria-label="Edit kode <?= esc($code['code']) ?>"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</button></td></tr><?php endforeach ?>
                        </tbody></table></div>
                    </div>
                </section>
        </div>
        <div class="col-12 col-lg-6"><?php include __DIR__ . '/v_masterrisk_category.php'; ?></div>
        <div class="col-12"><?php include __DIR__ . '/v_masterrisk_matrix.php'; ?></div>
    </div>
</div>
