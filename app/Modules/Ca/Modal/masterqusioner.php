<?php foreach (['instrument' => 'Instrumen', 'question' => 'Pertanyaan', 'preview' => 'Pratinjau kuesioner'] as $kind => $title): ?>
<div class="modal fade" id="ca-<?= $kind ?>-modal" tabindex="-1" aria-labelledby="ca-<?= $kind ?>-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="ca-<?= $kind ?>-title"><?= $title ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <?php if ($kind === 'preview'): ?>
                <div class="modal-body" id="ca-preview-content"></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
            <?php else: ?>
                <form id="ca-<?= $kind ?>-form" class="d-flex flex-column overflow-hidden">
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" role="alert"></div>
                        <input type="hidden" name="code">
                        <?php if ($kind === 'instrument'): ?>
                            <label for="ca-name" class="form-label required">Nama instrumen</label>
                            <input id="ca-name" name="name" class="form-control form-control-solid mb-4" maxlength="200" required>
                            <fieldset class="mb-5">
                                <legend class="fs-5 fw-bold">Penilaian skor</legend>
                                <p class="text-muted">Atur batas total skor untuk setiap kategori. Rentang harus berurutan. Kosongkan batas bawah pertama untuk mencakup semua skor di bawah batas atasnya.</p>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead><tr><th>Kategori</th><th>Skor minimum</th><th>Skor maksimum</th></tr></thead>
                                        <tbody>
                                            <?php foreach (['success', 'warning', 'danger'] as $index => $color): ?>
                                                <tr>
                                                    <td><span id="ca-risk-label-<?= $index ?>" class="badge badge-light-<?= $color ?>"></span></td>
                                                    <td><input name="risk_min_<?= $index ?>" type="number" min="0" max="999999" step="1" class="form-control form-control-solid" aria-label="Skor minimum kategori <?= $index + 1 ?>" <?= $index > 0 ? 'required' : '' ?>></td>
                                                    <td><input name="risk_max_<?= $index ?>" type="number" min="0" max="999999" step="1" required class="form-control form-control-solid" aria-label="Skor maksimum kategori <?= $index + 1 ?>"></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </fieldset>
                        <?php else: ?>
                            <input type="hidden" name="question_id">
                            <label for="ca-order" class="form-label required">Urutan</label>
                            <input id="ca-order" name="sort_order" type="number" class="form-control form-control-solid mb-4" min="1" max="9999" required>
                            <label for="ca-question" class="form-label required">Pertanyaan</label>
                            <textarea id="ca-question" name="question" class="form-control form-control-solid mb-4" rows="3" maxlength="4000" required></textarea>
                        <?php endif; ?>
                        <label for="ca-<?= $kind ?>-active" class="form-label">Status</label>
                        <select id="ca-<?= $kind ?>-active" name="active" class="form-select form-select-solid mb-4"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                        <?php if ($kind === 'question'): ?>
                            <fieldset>
                                <legend class="fs-5 fw-bold">Pilihan jawaban dan skor</legend>
                                <div id="ca-options"></div>
                                <button type="button" class="btn btn-sm btn-light-primary" id="ca-add-option">Tambah pilihan</button>
                            </fieldset>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
