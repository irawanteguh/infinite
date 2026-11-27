<div id="birth-page" data-url="<?= esc(site_url('satusehat/datakelahiran'), 'attr') ?>" data-csrf="<?= esc(csrf_hash(), 'attr') ?>">
    <header class="birth-banner app-geometric-banner app-module-banner p-6 mb-6">
        <div class="app-banner-heading">
            <h1 class="fw-bold mb-2">Transaksi Data Kelahiran</h1>
            <p class="mb-0">Pendaftaran kunjungan ibu dan bayi, pencatatan kelahiran, serta pelaporan akta lahir.</p>
        </div>
    </header>
    <div id="birth-status" class="alert alert-info" role="status" hidden></div>
    <section id="birth-list" class="card app-table-card">
        <div class="card-header app-table-heading">
            <div><h2 class="app-table-title">Daftar transaksi kelahiran</h2><p class="app-table-description">Data transaksi untuk organisasi aktif.</p></div>
            <div class="d-flex flex-wrap gap-3"><div class="app-table-search"><input id="birth-search" type="search" class="form-control" placeholder="Cari registrasi atau pasien" aria-label="Cari transaksi"></div></div>
        </div>
        <div class="card-body">
            <table id="birth-table" class="table table-row-dashed align-middle w-100 fs-8 gy-2" data-datatable="manual">
                <thead><tr><th>Registrasi</th><th>Ibu</th><th>Bayi</th><th>Diperbarui</th><th class="text-end">Tahap terkirim</th><th class="text-end">Actions</th></tr></thead><tbody></tbody>
            </table>
        </div>
    </section>
    <section id="birth-editor" hidden>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
            <button id="birth-back" class="btn btn-sm btn-light-primary" type="button"><i class="bi bi-arrow-left"></i>Daftar transaksi</button>
            <button id="birth-save" class="btn btn-sm btn-primary" type="button"><i class="bi bi-floppy"></i>Simpan draft</button>
        </div>
        <div class="card mb-5"><div class="card-body">
            <h2 class="fs-4 mb-5">Identitas transaksi</h2>
            <div class="row g-4">
                <div class="col-md-3"><label class="form-label" for="birth-number">Nomor registrasi</label><input id="birth-number" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label" for="birth-mother">Nama ibu</label><input id="birth-mother" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label" for="birth-baby">Nama bayi</label><input id="birth-baby" class="form-control"></div>
                <div class="col-md-3"><label class="form-label" for="birth-env">Environment</label><select id="birth-env" class="form-select"></select></div>
            </div>
        </div></div>
        <div class="row g-5">
            <aside class="col-lg-4"><div class="card birth-flow"><div class="card-body p-5"><h2 class="fs-5 mb-4">Tahapan transaksi</h2><div id="birth-stages" class="d-flex flex-column gap-4"></div></div></div></aside>
            <div class="col-lg-8"><div class="card"><div class="card-body">
                <h2 id="birth-action-title" class="fs-3 mb-2"></h2>
                <p id="birth-action-group" class="text-muted mb-5"></p>
                <div id="birth-action-error" class="alert alert-warning" hidden></div>
                <section class="mb-6"><h3 class="fs-5 mb-4">Pasien, tenaga kesehatan, dan referensi kunjungan</h3>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4"><label for="birth-lookup-mode" class="form-label">Pencarian pasien</label><select id="birth-lookup-mode" class="form-select"><option value="ihs">IHS</option><option value="nik">NIK</option></select></div>
                        <div class="col-md-8"><label id="birth-lookup-label" for="birth-identifier" class="form-label">Nomor IHS</label><div class="input-group"><input id="birth-identifier" class="form-control" autocomplete="off"><button id="birth-lookup" class="btn btn-light-primary" type="button">Cari</button></div></div>
                    </div>
                    <div id="birth-lookup-results" class="mb-4"></div>
                    <div id="birth-context" class="row g-4"></div>
                </section>
                <form id="birth-fields" class="row g-4"></form>
                <div class="d-flex justify-content-between align-items-center gap-3 mt-6 border-top pt-5">
                    <span id="birth-send-status" role="status"></span>
                    <button id="birth-send" type="button" class="btn btn-primary">Kirim tahap ini</button>
                </div>
                <div id="birth-result" class="mt-5" hidden><h3 class="fs-5">Hasil pengiriman</h3><div id="birth-result-text" class="birth-result"></div></div>
            </div></div></div>
        </div>
    </section>
</div>
