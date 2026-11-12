<div class="modal fade" id="pe-question-modal" tabindex="-1" aria-labelledby="pe-question-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <form id="pe-question-form">
            <div class="modal-header"><h3 id="pe-question-title" class="modal-title">Tambah pertanyaan</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger d-none pe-form-error" role="alert"></div>
                <input type="hidden" name="question_id"><input type="hidden" name="instrument_id">
                <div class="text-muted mb-5" id="pe-question-instrument"></div>
                <div class="row g-4 mb-5">
                    <div class="col-sm-3"><label for="pe-sort-order" class="form-label required">Urutan</label><input id="pe-sort-order" name="sort_order" type="number" min="1" max="9999" required class="form-control form-control-solid"></div>
                    <div class="col-sm-9"><label for="pe-domain" class="form-label required">Domain</label><select id="pe-domain" name="domain_id" required class="form-select form-select-solid"><option value="">Pilih domain</option></select><div class="form-text">Kelola pilihan melalui tombol Master domain pada halaman utama.</div></div>
                </div>
                <div class="mb-5"><label for="pe-question-text" class="form-label required">Pertanyaan / pernyataan</label><textarea id="pe-question-text" name="question" rows="4" maxlength="4000" required class="form-control form-control-solid" placeholder="Masukkan pernyataan yang akan dinilai pasien"></textarea></div>
                <div class="mb-5"><label for="pe-question-active" class="form-label">Status</label><select id="pe-question-active" name="active" class="form-select form-select-solid"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                <div class="bg-light rounded p-4 text-gray-600">Skala jawaban 1–5 mengikuti instrumen. Nonaktifkan pertanyaan bila tidak digunakan.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan pertanyaan</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="pe-domain-modal" tabindex="-1" aria-labelledby="pe-domain-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h3 id="pe-domain-title" class="modal-title">Master domain</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <form id="pe-domain-form" class="border rounded p-5 mb-6">
                <h4 id="pe-domain-form-title" class="mb-4">Tambah domain</h4>
                <div class="alert alert-danger d-none pe-form-error" role="alert"></div>
                <input type="hidden" name="domain_id">
                <div class="row g-4 mb-4">
                    <div class="col-sm-8"><label for="pe-domain-name" class="form-label required">Nama domain</label><input id="pe-domain-name" name="name" maxlength="150" required class="form-control form-control-solid"></div>
                    <div class="col-sm-4"><label for="pe-domain-active" class="form-label">Status</label><select id="pe-domain-active" name="active" class="form-select form-select-solid"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                </div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-sm btn-primary">Simpan domain</button><button type="button" id="pe-domain-reset" class="btn btn-sm btn-light">Batal / tambah baru</button></div>
            </form>
            <div class="table-responsive"><table class="table table-row-dashed align-middle gy-4"><thead><tr class="fw-bold text-muted"><th>Domain</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody id="pe-domain-list"></tbody></table></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div></div>
</div>
<div class="modal fade" id="pe-instrument-modal" tabindex="-1" aria-labelledby="pe-instrument-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <form id="pe-instrument-form">
            <div class="modal-header"><h3 id="pe-instrument-title" class="modal-title">Tambah instrumen</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger d-none pe-form-error" role="alert"></div>
                <input type="hidden" name="instrument_id">
                <div class="row g-4 mb-5">
                    <div class="col-sm-8"><label for="pe-instrument-code" class="form-label required">Kode</label><input id="pe-instrument-code" name="code" maxlength="50" pattern="[A-Z0-9_-]+" title="Gunakan huruf kapital, angka, garis bawah, atau tanda hubung" required class="form-control form-control-solid" placeholder="Contoh: PROM_SC"></div>
                    <div class="col-sm-4"><label for="pe-instrument-type" class="form-label required">Jenis</label><select id="pe-instrument-type" name="type" class="form-select form-select-solid"><option value="PROM">PROM</option><option value="PREM">PREM</option></select></div>
                </div>
                <div class="mb-5"><label for="pe-instrument-name" class="form-label required">Nama instrumen</label><input id="pe-instrument-name" name="name" maxlength="200" required class="form-control form-control-solid"></div>
                <div class="mb-5"><label for="pe-instrument-respondent" class="form-label required">Responden</label><input id="pe-instrument-respondent" name="respondent" maxlength="200" required class="form-control form-control-solid" placeholder="Contoh: Pasien/ibu sendiri"></div>
                <div class="mb-5"><label for="pe-instrument-measurement" class="form-label required">Waktu pengukuran</label><textarea id="pe-instrument-measurement" name="measurement" rows="2" maxlength="255" required class="form-control form-control-solid" placeholder="Contoh: T0 sebelum operasi; T1 saat pulang"></textarea></div>
                <div><label for="pe-instrument-active" class="form-label">Status</label><select id="pe-instrument-active" name="active" class="form-select form-select-solid"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan instrumen</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="pe-preview-modal" tabindex="-1" aria-labelledby="pe-preview-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h3 id="pe-preview-title" class="modal-title">Pratinjau kuesioner</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><div class="alert alert-primary">Pratinjau pertanyaan aktif. Jawaban pada tampilan ini tidak disimpan.</div><div id="pe-preview-content"></div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
    </div></div>
</div>
