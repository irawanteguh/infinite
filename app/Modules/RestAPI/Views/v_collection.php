<?php $scope = hash('sha256', json_encode([session()->get('groupid'), session()->get('orgid'), session()->get('userid')])); ?>
<div id="collection-page" data-storage-url="<?= esc(site_url('restapi/collection/storage'), 'attr') ?>" data-csrf-name="<?= esc(csrf_token(), 'attr') ?>" data-csrf-hash="<?= esc(csrf_hash(), 'attr') ?>" data-scope="<?= esc($scope, 'attr') ?>">
    <header class="mb-6"><h1>Collections</h1><p class="text-muted">Susun dan jalankan request REST API dengan environment Anda.</p></header>
    <div id="collection-status" class="alert alert-warning" role="status" hidden></div>
    <div class="row g-5">
        <aside class="col-lg-4 col-xl-3"><div class="card collection-tree-card"><div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button id="collection-new" type="button" class="btn btn-sm btn-primary">Tambah</button>
                <label class="btn btn-sm btn-light-primary mb-0">Impor JSON<input id="collection-import" type="file" accept=".json,application/json" class="d-none"></label>
            </div>
            <input id="collection-search" class="form-control mb-4" placeholder="Cari request…" aria-label="Cari request">
            <div id="collection-tree"></div>
        </div></div></aside>
        <section class="col-lg-8 col-xl-9" id="collection-request-panel"><div class="card"><div class="card-body">
            <div id="collection-request-tabs" class="d-flex gap-2 overflow-auto pb-3 mb-4" role="tablist" aria-label="Request terbuka"></div>
            <div class="d-flex flex-wrap gap-2 mb-5">
                <select id="request-method" class="form-select collection-method" aria-label="Method"><?php foreach (['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS'] as $method): ?><option><?= esc($method) ?></option><?php endforeach ?></select>
                <div class="collection-url">
                    <div id="request-url-highlight" class="collection-url-highlight" aria-hidden="true"></div>
                    <input id="request-url" class="form-control" placeholder="https:// atau {{base_url}}/Patient" aria-label="URL request" spellcheck="false" autocomplete="off">
                </div>
                <div class="btn-group" role="group" aria-label="Kirim dan simpan request">
                <button id="request-send" type="button" class="btn btn-primary">
                    <span id="request-send-label">Kirim</span>
                    <span id="request-send-progress" hidden><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Mengirim…</span>
                </button>
                    <button id="request-save-toggle" type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opsi request"></button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <button id="request-save" type="button" class="dropdown-item"><i class="bi bi-floppy" aria-hidden="true"></i>Simpan</button>
                        <button id="request-format-json" type="button" class="dropdown-item"><i class="bi bi-code-slash" aria-hidden="true"></i>Formatter JSON</button>
                    </div>
                </div>
                <button id="request-cancel" type="button" class="btn btn-light-danger" disabled>Batalkan</button>
                <button id="request-snippet" type="button" class="btn btn-light-primary"><i class="bi bi-code-slash me-1" aria-hidden="true"></i>PHP cURL</button>
            </div>
            <ul class="nav nav-tabs mb-5" role="tablist" aria-label="Konfigurasi request">
                <?php foreach (['params' => 'Params', 'authorization' => 'Authorization', 'headers' => 'Headers', 'body' => 'Body', 'scripts' => 'Script'] as $tab => $label): ?>
                    <li class="nav-item" role="presentation"><button type="button" id="request-tab-<?= $tab ?>" class="nav-link<?= $tab === 'params' ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#request-panel-<?= $tab ?>" role="tab" aria-controls="request-panel-<?= $tab ?>" aria-selected="<?= $tab === 'params' ? 'true' : 'false' ?>"><?= $label ?></button></li>
                <?php endforeach ?>
            </ul>
            <div class="tab-content">
                <div id="request-panel-params" class="tab-pane fade show active" role="tabpanel" aria-labelledby="request-tab-params">
                    <h3 class="fs-6 mb-3">Query Params</h3>
                    <div id="request-param-fields"></div>
                    <button id="request-param-add" type="button" class="btn btn-sm btn-primary mt-2"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah parameter</button>
                    <div id="request-path-section" class="mt-6" hidden>
                        <h3 class="fs-6 mb-3">Path Variables</h3>
                        <div id="request-path-fields"></div>
                    </div>
                </div>
                <div id="request-panel-headers" class="tab-pane fade" role="tabpanel" aria-labelledby="request-tab-headers">
                    <label for="request-headers" class="form-label">Headers · JSON object</label><textarea id="request-headers" class="form-control font-monospace" rows="7" spellcheck="false"></textarea>
                </div>
                <div id="request-panel-body" class="tab-pane fade" role="tabpanel" aria-labelledby="request-tab-body">
                    <label for="request-body-mode" class="form-label">Body</label>
                    <select id="request-body-mode" class="form-select mb-3"><option value="none">None</option><option value="raw">Raw</option><option value="urlencoded">x-www-form-urlencoded</option></select>
                    <textarea id="request-body" aria-label="Raw body" class="form-control font-monospace" rows="7" spellcheck="false"></textarea>
                    <div id="request-form-body" hidden><div id="request-body-fields"></div><button id="request-body-add" type="button" class="btn btn-sm btn-primary mt-2"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah parameter</button></div>
                </div>
                <div id="request-panel-authorization" class="tab-pane fade" role="tabpanel" aria-labelledby="request-tab-authorization">
                    <label for="request-token" class="form-label">Bearer token</label><input id="request-token" type="password" class="form-control" placeholder="{{access_token}}" autocomplete="off">
                    <p class="text-muted mt-3 mb-0">Token berlaku untuk request yang sedang dibuka.</p>
                </div>
                <div id="request-panel-scripts" class="tab-pane fade" role="tabpanel" aria-labelledby="request-tab-scripts">
                    <p class="text-muted">After Response mendukung pm.response.json() dan pm.environment.set() untuk nilai properti JSON. Before Request ditampilkan sebagai referensi.</p>
                    <label for="request-prerequest" class="form-label">Before Request</label><textarea id="request-prerequest" class="form-control font-monospace mb-4" rows="5" readonly></textarea>
                    <label for="request-tests" class="form-label">After Response</label><textarea id="request-tests" class="form-control font-monospace" rows="5" readonly></textarea>
                </div>
            </div>
            <hr class="my-6">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <h2 class="fs-4 mb-0">Response</h2>
                <span id="response-meta" class="collection-response-meta" data-tone="waiting" role="status">Pilih request untuk mulai.</span>
            </div>
            <ul class="nav nav-tabs mb-4" role="tablist" aria-label="Response">
                <li class="nav-item" role="presentation">
                    <button type="button" id="response-tab-body" class="nav-link active" data-bs-toggle="tab" data-bs-target="#response-panel-body" role="tab" aria-controls="response-panel-body" aria-selected="true">Body</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" id="response-tab-headers" class="nav-link" data-bs-toggle="tab" data-bs-target="#response-panel-headers" role="tab" aria-controls="response-panel-headers" aria-selected="false">Headers</button>
                </li>
            </ul>
            <div class="tab-content">
                <div id="response-panel-body" class="tab-pane fade show active" role="tabpanel" aria-labelledby="response-tab-body">
                    <div class="collection-response-editor">
                        <div class="collection-response-gutter" aria-hidden="true"><div id="response-line-numbers">1</div></div>
                        <pre id="response-body" class="collection-response p-4 rounded bg-light" tabindex="0"></pre>
                    </div>
                </div>
                <div id="response-panel-headers" class="tab-pane fade" role="tabpanel" aria-labelledby="response-tab-headers">
                    <div class="table-responsive collection-response-headers">
                        <table class="table table-row-dashed align-middle mb-0" aria-label="Response headers" data-datatable="manual">
                            <tbody id="response-headers"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div></div></section>
    </div>
</div>

<div class="modal fade" id="collection-environment-modal" tabindex="-1" aria-labelledby="collection-environment-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="collection-environment-title" class="modal-title fs-4">Environment</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="collection-environment-status" role="status"></div>
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle w-100 mb-0" data-datatable="manual" aria-label="Variabel environment">
                        <thead><tr><th>Aktif</th><th>Variable</th><th>Type</th><th>Value</th></tr></thead>
                        <tbody id="collection-environment-values"></tbody>
                    </table>
                </div>
                <button id="add-modal-environment-variable" type="button" class="btn btn-sm btn-primary mt-4" disabled><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah variabel</button>
            </div>
            <div class="modal-footer"><button id="save-modal-environment" type="button" class="btn btn-primary" disabled>Simpan</button><button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="request-snippet-modal" tabindex="-1" aria-labelledby="request-snippet-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-4" id="request-snippet-title">Code snippet · PHP cURL</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Snippet memakai nilai environment aktif, termasuk token dan secret.</p>
                <textarea id="request-snippet-code" class="form-control font-monospace" rows="20" readonly spellcheck="false" aria-label="Kode PHP cURL"></textarea>
            </div>
            <div class="modal-footer">
                <button id="request-snippet-copy" type="button" class="btn btn-primary">Salin</button>
                <button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/restapi/storage.js?v=' . filemtime(FCPATH . 'assets/js/restapi/storage.js')) ?>"></script>
