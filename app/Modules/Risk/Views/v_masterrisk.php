<?php
$error = session()->getFlashdata('masterrisk_error');
$success = session()->getFlashdata('masterrisk_success');
$unitCount = count(array_unique(array_filter(array_column($codes, 'department_id'))));
$unmappedCount = count(array_filter($codes, static function ($code) { return empty($code['department']); }));
?>
<div class="d-flex flex-column gap-6">
    <?php if ($error): ?><div class="alert alert-danger mb-0" role="alert"><?= esc($error) ?></div><?php endif ?>
    <?php if ($success): ?><div class="alert alert-success mb-0" role="status"><?= esc($success) ?></div><?php endif ?>
    <section class="card card-flush border-0 bg-light-primary">
        <div class="card-body p-6 p-lg-7 d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-50px"><span class="symbol-label bg-white"><i class="bi bi-shield-check text-primary fs-2x" aria-hidden="true"></i></span></div>
                <div><h1 class="fs-2 fw-bolder text-gray-900 mb-1">Master Risiko</h1><p class="text-gray-600 mb-0">Kode department, kategori, dan matriks penilaian risiko.</p></div>
            </div>
            <span class="badge badge-light-warning px-4 py-3">Ambang penanganan ≥12</span>
        </div>
    </section>
    <div class="row g-4">
        <?php foreach ([
            ['codes', 'collection', 'primary', count($codes), 'Kode risiko', $unitCount . ' department terhubung'],
            ['categories', 'grid', 'info', count($categories), 'Kategori risiko', 'Definisi dan keterangan kategori'],
            ['matrix', 'table', 'success', count($matrixCells), 'Kombinasi matriks', 'Kemungkinan × dampak · 5 × 5'],
        ] as $stat): ?>
        <div class="col-md-4"><a href="#master-<?= $stat[0] ?>" data-master-section="<?= $stat[0] ?>" class="card card-flush h-100 border border-hover-<?= $stat[2] ?> text-decoration-none">
            <div class="card-body p-5 d-flex align-items-start gap-4">
                <div class="symbol symbol-40px"><span class="symbol-label bg-light-<?= $stat[2] ?>"><i class="bi bi-<?= $stat[1] ?> text-<?= $stat[2] ?> fs-3" aria-hidden="true"></i></span></div>
                <div><div class="d-flex align-items-baseline gap-3 mb-1"><strong class="fs-2 fw-bolder text-gray-900"><?= $stat[3] ?></strong><span class="fw-bold text-gray-700"><?= $stat[4] ?></span></div><div class="fs-8 text-gray-600"><?= $stat[5] ?></div></div>
            </div>
        </a></div>
        <?php endforeach ?>
    </div>
    <div class="card card-flush border">
        <div class="card-header px-5 px-lg-7 border-bottom">
            <ul class="nav nav-line-tabs nav-line-tabs-2x fs-6 fw-bold border-0 gap-5" role="tablist" aria-label="Master risiko">
                <li class="nav-item" role="presentation"><button class="nav-link active py-5 bg-transparent border-top-0 border-start-0 border-end-0" id="master-codes-tab" data-bs-toggle="tab" data-bs-target="#master-codes-pane" type="button" role="tab" aria-controls="master-codes-pane" aria-selected="true"><i class="bi bi-collection text-primary me-2" aria-hidden="true"></i>Kode Risiko</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link py-5 bg-transparent border-top-0 border-start-0 border-end-0" id="master-categories-tab" data-bs-toggle="tab" data-bs-target="#master-categories-pane" type="button" role="tab" aria-controls="master-categories-pane" aria-selected="false"><i class="bi bi-grid text-info me-2" aria-hidden="true"></i>Kategori Risiko</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link py-5 bg-transparent border-top-0 border-start-0 border-end-0" id="master-matrix-tab" data-bs-toggle="tab" data-bs-target="#master-matrix-pane" type="button" role="tab" aria-controls="master-matrix-pane" aria-selected="false"><i class="bi bi-table text-success me-2" aria-hidden="true"></i>Matriks 5 × 5</button></li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="master-codes-pane" role="tabpanel" aria-labelledby="master-codes-tab" tabindex="0">
                <section class="card card-flush" id="master-codes">
                    <div class="card-header border-0 pt-6 align-items-center gap-3"><div><h2 class="fs-5 mb-1">Kode risiko per department</h2><span class="text-muted fs-7"><?= count($codes) ?> kode terdaftar</span></div><div class="position-relative my-1 w-100 w-md-250px"><i class="bi bi-search fs-3 position-absolute top-50 start-0 translate-middle-y ms-4 text-muted" aria-hidden="true"></i><label for="mr-search" class="visually-hidden">Cari kode atau department</label><input id="mr-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kode atau department…"></div></div>
                    <div class="card-body pt-4">
                        <?php if (!$ready): ?><div class="alert alert-warning">Tabel master kode risiko belum tersedia.</div><?php endif ?>
                        <?php if ($unmappedCount): ?><div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-4 mb-5"><i class="bi bi-info-circle text-warning fs-3 me-3" aria-hidden="true"></i><span class="text-gray-800"><?= $unmappedCount ?> kode belum terhubung ke department.</span></div><?php endif ?>
                        <div class="table-responsive"><table id="datamasterrisk_table" class="table align-middle table-row-dashed gy-2"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-50px">#</th><th class="w-150px">Kode risiko</th><th>Department</th><th class="text-end pe-4 rounded-end w-100px">Aksi</th></tr></thead><tbody class="fw-bold text-gray-600" id="mr-codes">
                        <?php foreach ($codes as $index => $code): ?><tr data-master-code><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-primary"><?= esc($code['code']) ?></span></td><td><?php if (!empty($code['department'])): ?><?= esc($code['department']) ?><?php else: ?><span class="badge badge-light-warning">Belum dipetakan</span><?php endif ?></td><td class="text-end pe-4"><button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modal_add_masterrisk" data-code-id="<?= esc($code['code_id']) ?>" data-code="<?= esc($code['code']) ?>" data-department-id="<?= esc($code['department_id']) ?>" aria-label="Edit kode <?= esc($code['code']) ?>"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</button></td></tr><?php endforeach ?>
                        </tbody></table></div>
                    </div>
                </section>
            </div>
            <div class="tab-pane fade" id="master-categories-pane" role="tabpanel" aria-labelledby="master-categories-tab" tabindex="0"><?php include __DIR__ . '/v_masterrisk_category.php'; ?></div>
            <div class="tab-pane fade" id="master-matrix-pane" role="tabpanel" aria-labelledby="master-matrix-tab" tabindex="0"><?php include __DIR__ . '/v_masterrisk_matrix.php'; ?></div>
        </div>
    </div>
</div>
