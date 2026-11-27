<?php foreach (['create-instrument' => 'Tambah Instrumen', 'instrument' => 'Edit Instrumen', 'question' => 'Pertanyaan', 'preview' => 'Pratinjau kuesioner'] as $kind => $title): ?>
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
                        <?php if ($kind === 'create-instrument'): ?>
                            <label for="ca-new-code" class="form-label required">Kode instrumen</label>
                            <input id="ca-new-code" name="code" class="form-control form-control-solid" maxlength="10" pattern="[A-Za-z][A-Za-z0-9_\x2D]{0,9}" aria-describedby="ca-new-code-help" required>
                            <div id="ca-new-code-help" class="form-text mb-4">Maksimal 10 karakter. Awali dengan huruf. Karakter berikutnya boleh berupa huruf, angka, garis bawah, atau tanda hubung.</div>
                            <label for="ca-new-name" class="form-label required">Nama instrumen</label>
                            <input id="ca-new-name" name="name" class="form-control form-control-solid mb-4" maxlength="200" required>
                            <p class="text-muted mb-0">Instrumen disimpan sebagai nonaktif. Setelah tersimpan, Anda bisa menambahkan pertanyaan dan membuka pratinjau.</p>
                        <?php else: ?>
                            <input type="hidden" name="code">
                            <?php if ($kind === 'instrument'): ?>
                                <label for="ca-name" class="form-label required">Nama instrumen</label>
                                <input id="ca-name" name="name" class="form-control form-control-solid mb-4" maxlength="200" required>
                                <fieldset id="ca-instrument-risks" class="mb-5">
                                    <legend class="fs-5 fw-bold">Penilaian skor</legend>
                                    <p class="text-muted">Atur batas total skor untuk setiap kategori. Rentang harus berurutan. Kosongkan batas bawah pertama untuk mencakup semua skor di bawah batas atasnya.</p>
                                    <p id="ca-optional-risks" class="text-muted d-none">Kosongkan seluruh batas skor jika penilaian belum ditetapkan untuk instrumen ini.</p>
                                    <div class="table-responsive">
                                        <table class="table align-middle fs-8 gy-2" data-datatable="false">
                                            <thead><tr><th>Kategori</th><th>Skor minimum</th><th>Skor maksimum</th></tr></thead>
                                            <tbody>
                                                <?php foreach (['success', 'warning', 'danger'] as $index => $color): ?>
                                                    <tr>
                                                        <td><span class="badge badge-light-<?= $color ?>"><?= esc(\Modules\Ca\Config\ScreeningMasters::RISK_LABELS[$index]) ?></span></td>
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
                                    <p id="ca-answer-type-help" class="text-muted d-none">Pilih Skor, Tanpa skor, atau Tanda peringatan untuk setiap jawaban. Tanda peringatan disimpan tanpa skor.</p>
                                    <div id="ca-options"></div>
                                    <button type="button" class="btn btn-sm btn-light-primary" id="ca-add-option">Tambah pilihan</button>
                                </fieldset>
                            <?php endif; ?>
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
