<?php $categoryError = session()->getFlashdata('masterrisk_category_error'); ?>
<section class="card card-flush h-100 border shadow-sm" id="master-categories" style="border-top:3px solid #8b5cf6 !important">
    <div class="card-header border-0 pt-6 align-items-center gap-3">
        <div><h2 class="fs-5 mb-2"><i class="bi bi-grid text-info me-2" aria-hidden="true"></i>Kategori risiko</h2><span class="text-muted fs-7">Kelompokkan risiko dengan definisi yang konsisten.</span></div>
        <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="position-relative my-1 w-100 w-md-250px">
            <i class="bi bi-search fs-3 position-absolute top-50 start-0 translate-middle-y ms-4 text-muted" aria-hidden="true"></i>
            <label for="mr-category-search" class="visually-hidden">Cari kategori atau keterangan</label>
            <input id="mr-category-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kategori atau keterangan…">
        </div>
        </div>
    </div>
    <div class="card-body pt-5">
        <?php if (!$categoriesReady): ?><div class="alert alert-warning">Master kategori risiko belum tersedia. Hubungi administrator untuk menyiapkannya.</div><?php endif ?>
        <div class="table-responsive"><table id="datamasterriskcategory_table" class="table align-middle table-row-dashed gy-2"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-75px">#</th><th class="min-w-200px">Kategori risiko</th><th class="min-w-200px rounded-end">Keterangan</th></tr></thead><tbody class="fw-bold text-gray-600">
        <?php foreach ($categories as $index => $category): ?><tr><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-primary text-wrap text-start lh-base"><?= esc($category['name']) ?></span></td><td class="lh-lg"><?php if (!empty($category['description'])): ?><?= nl2br(esc($category['description'])) ?><?php else: ?><span class="text-muted fw-normal">Belum ada keterangan.</span><?php endif ?></td></tr><?php endforeach ?>
        </tbody></table></div>
    </div>
</section>
