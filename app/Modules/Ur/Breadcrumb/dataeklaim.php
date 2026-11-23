<?php if (isset($reviewFilters)): ?>
<?php
    $chartPage = $isReviewCharts ?? false;
    $scope = http_build_query(['year' => $reviewFilters['year'], 'service' => $reviewFilters['service']]);
    $queueUrl = site_url('ur/dataeklaim/review') . '?' . $scope;
    $chartsUrl = site_url('ur/dataeklaim/reviewcharts') . '?' . $scope;
?>
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
        <li class="breadcrumb-item"><a class="text-muted text-hover-primary" href="<?= esc(site_url('ur/dataeklaim'), 'attr') ?>">Data E-Klaim</a></li>
        <li class="breadcrumb-item" aria-hidden="true"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
        <?php if ($chartPage): ?>
            <li class="breadcrumb-item"><a class="text-muted text-hover-primary" href="<?= esc($queueUrl, 'attr') ?>">Review Verifikator Internal</a></li>
        <?php else: ?>
            <li class="breadcrumb-item text-dark" aria-current="page">Review Verifikator Internal</li>
        <?php endif; ?>
        <li class="breadcrumb-item" aria-hidden="true"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
        <?php if ($chartPage): ?>
            <li class="breadcrumb-item text-dark" aria-current="page">Tren Temuan</li>
        <?php else: ?>
            <li class="breadcrumb-item"><a class="text-muted text-hover-primary" href="<?= esc($chartsUrl, 'attr') ?>">Tren Temuan</a></li>
        <?php endif; ?>
    </ol>
</nav>
<?php endif; ?>
