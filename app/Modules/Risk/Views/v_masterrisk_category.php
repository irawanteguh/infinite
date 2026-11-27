<?php $categoryError = session()->getFlashdata('masterrisk_category_error'); ?>
<section class="card card-flush h-100 border shadow-sm" id="master-categories" >
    <div class="card-header border-0 pt-6 align-items-center gap-3">
        <div><h2 class="fs-5 mb-2">Kategori risiko</h2><p>Kelompokkan risiko dengan definisi yang konsisten.</p></div>
        <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="position-relative risk-master-search my-1 w-100 w-md-250px">
            <svg class="risk-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 4.5 4.5"></path></svg>
            <label for="mr-category-search" class="visually-hidden">Cari kategori atau keterangan</label>
            <input id="mr-category-search" type="search" class="form-control form-control-solid ps-12" placeholder="Cari kategori atau keterangan…">
        </div>
        </div>
    </div>
    <div class="card-body pt-5">
        <?php if (!$categoriesReady): ?><div class="alert alert-warning">Master kategori risiko belum tersedia. Hubungi administrator untuk menyiapkannya.</div><?php endif ?>
        <div class="table-responsive"><table id="datamasterriskcategory_table" class="table align-middle table-row-dashed gy-2 fs-8"><thead><tr class="fw-bolder text-muted bg-light"><th class="text-start ps-4 rounded-start w-75px">#</th><th class="min-w-200px">Kategori risiko</th><th class="min-w-200px rounded-end">Keterangan</th></tr></thead><tbody class="fw-bold text-gray-600">
        <?php foreach ($categories as $index => $category): ?><tr><td class="ps-4"><?= $index + 1 ?></td><td><span class="badge badge-light-primary text-wrap text-start lh-base"><?= esc($category['name']) ?></span></td><td class="lh-lg"><?php if (!empty($category['description'])): ?><?= nl2br(esc($category['description'])) ?><?php else: ?><span class="text-muted fw-normal">Belum ada keterangan.</span><?php endif ?></td></tr><?php endforeach ?>
        </tbody></table></div>
    </div>
</section>
