<?php include APPPATH . 'Modules/Ur/Toolbar/_common.php'; ?>
<div class="module-toolbar-actions"><button type="button" class="btn btn-sm btn-light-primary ur-button" id="tp-refresh"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Perbarui</button><button type="button" class="btn btn-sm btn-light-primary ur-button" id="tp-print"><i class="bi bi-printer" aria-hidden="true"></i> <?= (service('uri')->getSegments()[2] ?? '') === 'detail' ? 'Cetak daftar kasus' : 'Cetak ringkasan' ?></button></div>

