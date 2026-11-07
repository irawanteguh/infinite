<?php
$fishboneId = (string) request()->getGet('fishbone_id');
$listUrl = site_url('qi/fishbone');
?>
<ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
    <?php if ($fishboneId !== ''): ?>
        <li class="breadcrumb-item text-muted"><a href="<?= esc($listUrl) ?>" class="text-muted text-hover-primary">Daftar Analisis</a></li>
        <li class="breadcrumb-item"><span class="bullet bg-gray-200 w-5px h-2px"></span></li>
        <li class="breadcrumb-item text-dark">Diagram sebab-akibat</li>
    <?php endif; ?>
</ul>
