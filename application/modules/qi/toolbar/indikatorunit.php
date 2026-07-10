<?php if (!$this->input->get('uuid')) : ?>
    <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_pengajuanindikatorunit"><i class="bi bi-file-earmark-plus"></i> Pengajuan Indikator Unit</a>
<?php else : ?>
    <a href="<?= site_url('qi/indikatorunit') ?>" class="btn btn-sm btn-secondary me-4"><i class="bi bi-arrow-left"></i> Back to List Indicator</a>
    <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_pengajuanindikatorunit"><i class="bi bi-file-earmark-plus"></i> Submit</a>
<?php endif; ?>