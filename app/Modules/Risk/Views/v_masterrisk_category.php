<?php $categoryError = session()->getFlashdata('masterrisk_category_error'); ?>
<section class="card card-flush" id="master-categories">
    <div class="card-header border-0 pt-6 align-items-center gap-3">
        <div><h3 class="mb-1">Master kategori risiko</h3><span class="text-muted"><?= count($categories) ?> kategori risiko organisasi</span></div>
        <div class="position-relative my-1 w-100 w-md-250px">
            <i class="bi bi-search fs-3 position-absolute top-50 start-0 translate-middle-y ms-4 text-muted" aria-hidden="true"></i>
            <label for="mr-category-search" class="visually-hidden">Cari kategori atau keterangan</label>
            <input id="mr-category-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kategori atau keterangan…">
        </div>
    </div>
    <div class="card-body pt-5">
        <?php if ($categoryError): ?><div class="alert alert-danger" role="alert"><?= esc($categoryError) ?></div><?php endif ?>
        <?php if (!$categoriesReady): ?><div class="alert alert-warning">Tabel master kategori risiko belum tersedia. Jalankan migrasi modul terlebih dahulu.</div><?php endif ?>
        <div class="table-responsive"><table id="datamasterriskcategory_table" class="table align-middle table-row-dashed gy-2"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-75px">#</th><th class="min-w-200px">Kategori risiko</th><th class="min-w-300px rounded-end">Keterangan</th></tr></thead><tbody class="fw-bold text-gray-600">
        <?php foreach ($categories as $index => $category): ?><tr><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-<?= ['primary', 'success', 'info', 'warning'][$index % 4] ?> text-wrap text-start lh-base"><?= esc($category['name']) ?></span></td><td class="lh-lg"><?= nl2br(esc($category['description'] ?: 'Belum tersedia pada referensi Excel.')) ?></td></tr><?php endforeach ?>
        </tbody></table></div>
    </div>
</section>
