<?php $isDetail = (service('uri')->getSegments()[2] ?? '') === 'detail'; ?>
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
        <?php if ($isDetail): ?>
            <li class="breadcrumb-item text-muted"><a id="icu-summary-link" href="<?= esc(site_url('ur/icu')) ?>" class="text-muted text-hover-primary">Ringkasan</a></li>
        <?php else: ?>
            <li class="breadcrumb-item text-dark" aria-current="page">Ringkasan</li>
        <?php endif; ?>
        <li class="breadcrumb-item" aria-hidden="true"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
        <?php if ($isDetail): ?>
            <li class="breadcrumb-item text-dark" aria-current="page">Daftar kasus</li>
        <?php else: ?>
            <li class="breadcrumb-item text-muted"><a id="icu-detail-link" href="<?= esc(site_url('ur/icu/detail')) ?>" class="text-muted text-hover-primary">Daftar kasus</a></li>
        <?php endif; ?>
    </ol>
</nav>
