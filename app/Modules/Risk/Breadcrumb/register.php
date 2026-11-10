<?php
$isPeta = (service('uri')->getSegments()[2] ?? '') === 'peta';
$context = $riskPetaContext ?? [];
?>
<ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1" aria-label="Breadcrumb">
    <li class="breadcrumb-item"><a href="<?= site_url('risk/register') ?>" class="text-muted text-hover-primary">Register Risiko</a></li>
    <?php if ($isPeta): ?>
    <li class="breadcrumb-item"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Peta Risiko</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-dark" aria-current="page"><?= esc($context['unitName'] ?? 'Semua unit') ?> · <?= esc((string) ($context['year'] ?? '')) ?></li>
    <?php endif ?>
</ul>
