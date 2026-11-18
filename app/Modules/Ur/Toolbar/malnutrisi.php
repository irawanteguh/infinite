<?php include APPPATH . 'Modules/Ur/Toolbar/_common.php'; ?>
<div class="module-toolbar-actions"><button id="mal-refresh" type="button" class="btn btn-sm btn-light-primary ur-button"><i class="bi bi-arrow-clockwise"></i> Perbarui</button><button id="mal-print" type="button" class="btn btn-sm btn-light-primary ur-button"><i class="bi bi-printer"></i> <?= (service('uri')->getSegments()[2] ?? '') === 'detail' ? 'Cetak daftar kasus' : 'Cetak ringkasan' ?></button></div>

