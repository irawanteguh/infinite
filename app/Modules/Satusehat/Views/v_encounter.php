<div id="encounter-page" data-url="<?= esc(site_url('satusehat/encounter'), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>">
    <header class="encounter-banner app-geometric-banner p-6 p-md-8 mb-6">
        <h1 class="fw-bold mb-2">Pendaftaran Encounter</h1>
        <p class="mb-0">Daftarkan kunjungan pasien dan pantau pengirimannya ke SATUSEHAT.</p>
    </header>
    <div id="encounter-message" class="alert alert-info" role="status" hidden></div>
    <section id="encounter-list" class="card app-table-card">
        <div class="card-header app-table-heading">
            <div><h2 class="app-table-title">Daftar kunjungan</h2><p class="app-table-description">Pendaftaran pada organisasi aktif beserta status pengiriman.</p></div>
            <div class="app-table-search"><input id="encounter-search" type="search" class="form-control" placeholder="Cari registrasi, pasien, atau IHS" aria-label="Cari kunjungan"></div>
        </div>
        <div class="card-body">
            <table id="encounter-table" class="table table-row-dashed align-middle w-100" data-datatable="manual">
                <thead><tr><th>Registrasi</th><th>Pasien</th><th>Jenis kunjungan</th><th>Environment</th><th>Status</th><th>ID Encounter</th><th class="text-end">Actions</th></tr></thead><tbody></tbody>
            </table>
        </div>
    </section>
    <section id="encounter-editor" hidden>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5">
            <button id="encounter-back" type="button" class="btn btn-sm btn-light-primary"><i class="bi bi-arrow-left" aria-hidden="true"></i>Daftar kunjungan</button>
            <div class="d-flex gap-3">
                <button id="encounter-save" type="button" class="btn btn-sm btn-light-primary"><i class="bi bi-floppy" aria-hidden="true"></i>Simpan draft</button>
                <button id="encounter-send" type="button" class="btn btn-sm btn-primary"><i class="bi bi-send" aria-hidden="true"></i>Kirim ke SATUSEHAT</button>
            </div>
        </div>
        <form id="encounter-form">
            <div class="card mb-5"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
                    <h2 class="fs-4 mb-0">Informasi kunjungan</h2><span id="encounter-state" class="encounter-state"></span>
                </div>
                <div class="row g-5">
                    <div class="col-md-6"><label class="form-label" for="encounter-number">Nomor registrasi</label><input id="encounter-number" class="form-control form-control-solid" placeholder="Otomatis saat draft disimpan" readonly></div>
                    <div class="col-md-6"><label class="form-label" for="encounter-environment">Environment</label><select id="encounter-environment" class="form-select" required></select></div>
                    <div class="col-md-6"><label class="form-label" for="encounter-class">Jenis kunjungan</label><select id="encounter-class" class="form-select" required><option value="">Pilih jenis kunjungan</option><option value="EMER">Emergency</option><option value="AMB">Outpatient</option><option value="IMP">Inpatient</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="encounter-arrived">Waktu kedatangan</label><input id="encounter-arrived" type="datetime-local" class="form-control" required><div class="form-text">Waktu lokal perangkat, disimpan sebagai UTC.</div></div>
                </div>
            </div></div>
            <div class="row g-5 mb-5">
                <?php foreach (['patient' => 'Pasien', 'practitioner' => 'Practitioner'] as $key => $label): ?>
                    <section class="col-xl-6"><div class="card h-100"><div class="card-body">
                        <h2 class="fs-4 mb-5"><?= esc($label) ?></h2>
                        <div class="mb-5">
                            <label class="form-label" for="encounter-<?= $key ?>-identifier">Cari master <?= esc($label) ?></label>
                            <div class="input-group">
                                <input id="encounter-<?= $key ?>-identifier" class="form-control" placeholder="Nama, NIK, atau IHS" autocomplete="off">
                                <button type="button" class="btn btn-light-primary" id="encounter-<?= $key ?>-find">Cari master</button>
                            </div>
                        </div>
                        <div class="mb-4"><label class="form-label" for="encounter-<?= $key ?>-name">Nama <?= esc($label) ?></label><input id="encounter-<?= $key ?>-name" class="form-control form-control-solid" readonly></div>
                        <div><label class="form-label" for="encounter-<?= $key ?>-ihs">ID SATUSEHAT <?= esc($label) ?></label><input id="encounter-<?= $key ?>-ihs" class="form-control form-control-solid" readonly></div>
                        <?php if ($key === 'practitioner'): ?><button id="encounter-practitioner-clear" class="btn btn-sm btn-light mt-4" type="button">Kosongkan Practitioner</button><?php endif ?>
                    </div></div></section>
                <?php endforeach ?>
            </div>
            <section class="card mb-5"><div class="card-body">
                <h2 class="fs-4 mb-5">Poli / lokasi pelayanan</h2>
                <div class="row g-5">
                    <div class="col-md-6"><div class="form-label">Master Location organisasi</div><button id="encounter-location-find" type="button" class="btn btn-light-primary"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>Pilih lokasi</button></div>
                    <div class="col-md-6"><label for="encounter-location-name" class="form-label">Lokasi terpilih</label><input id="encounter-location-name" class="form-control form-control-solid" readonly></div>
                </div>
            </div></section>
        </form>
        <section id="encounter-result" class="card mb-5" hidden><div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4"><h2 class="fs-4 mb-0">Hasil pengiriman</h2><button id="encounter-reconcile" class="btn btn-sm btn-light-primary" type="button"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Periksa status</button></div>
            <div id="encounter-result-content"></div>
        </div></section>
    </section>
    <div class="modal fade" id="encounter-lookup-modal" tabindex="-1" aria-labelledby="encounter-lookup-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="encounter-lookup-title" class="modal-title fs-4">Pilih pasien</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div id="encounter-lookup-body" class="modal-body">
            <section id="encounter-results" class="card app-table-card mb-5" hidden>
                <div class="card-header app-table-heading">
                    <div><h2 id="encounter-results-title" class="app-table-title">Hasil pencarian</h2><p id="encounter-results-description" class="app-table-description"></p></div>
                    <div class="app-table-search"><input id="encounter-results-search" type="search" class="form-control" placeholder="Cari hasil" aria-label="Cari hasil pencarian"></div>
                </div>
                <div class="card-body"><table id="encounter-results-table" class="table table-row-dashed align-middle w-100" data-datatable="manual"><thead></thead><tbody></tbody></table></div>
            </section>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="encounter-room-modal" tabindex="-1" aria-labelledby="encounter-room-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="encounter-room-form">
                <div class="modal-header">
                    <h2 id="encounter-room-title" class="modal-title fs-4">Masuk ruang (Ibu)</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p id="encounter-room-patient" class="fw-semibold mb-5"></p>
                    <div id="encounter-room-message" class="alert alert-warning" role="status" hidden></div>
                    <section class="card app-table-card mb-5" aria-labelledby="encounter-room-episodes-title">
                        <div class="card-header app-table-heading">
                            <div><h3 id="encounter-room-episodes-title" class="app-table-title">Episode ANC aktif</h3><p class="app-table-description">Pilih episode pelayanan pasien untuk kunjungan ini.</p></div>
                            <div class="app-table-search"><input id="encounter-room-episodes-search" class="form-control" type="search" placeholder="Cari episode ANC" aria-label="Cari episode ANC"></div>
                        </div>
                        <div class="card-body">
                            <table id="encounter-room-episodes" class="table table-row-dashed align-middle w-100" data-datatable="manual">
                                <thead><tr><th>Pilih</th><th>ID episode ANC</th><th>Status</th><th>Mulai</th><th>Selesai</th></tr></thead><tbody></tbody>
                            </table>
                        </div>
                    </section>
                    <div class="mb-5">
                        <label for="encounter-room-location" class="form-label">Ruang pelayanan</label>
                        <select id="encounter-room-location" class="form-select" required></select>
                    </div>
                    <div>
                        <label for="encounter-room-time" class="form-label">Waktu masuk ruang</label>
                        <input id="encounter-room-time" type="datetime-local" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button id="encounter-room-save" type="submit" class="btn btn-primary">Simpan masuk ruang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="encounter-exchange-modal" tabindex="-1" aria-labelledby="encounter-exchange-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 id="encounter-exchange-title" class="modal-title fs-4">Request dan response Encounter</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <p id="encounter-exchange-status" class="text-muted"></p>
            <?php foreach (['request' => 'Request body', 'response' => 'Response body'] as $key => $label): ?>
                <h3 class="fs-6"><?= esc($label) ?></h3>
                <div class="encounter-json border rounded bg-light mb-5" tabindex="0" role="region" aria-label="<?= esc($label, 'attr') ?>">
                    <pre id="encounter-json-<?= $key ?>-lines" class="encounter-json-lines mb-0 p-3" aria-hidden="true"></pre>
                    <pre id="encounter-json-<?= $key ?>" class="mb-0 p-3"></pre>
                </div>
            <?php endforeach ?>
        </div>
        <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Tutup</button></div>
    </div></div>
</div>
